<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace ShoppingFlux\EventListeners;
use ShoppingFlux\API\Exception\BadResponseException;
use ShoppingFlux\API\Request;
use ShoppingFlux\API\UpdateOrders;
use ShoppingFlux\API\ValidOrders;
use ShoppingFlux\Event\ApiCallEvent;
use ShoppingFlux\Event\ShoppingFluxEvents;
use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Address\AddressCreateOrUpdateEvent;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\Order\OrderManualEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Log\Tlog;
use Thelia\Model\LangQuery;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatusQuery;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Model\Cart;
use Thelia\Model\CartItem;
use Thelia\Model\CountryQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\CustomerTitleQuery;
use Thelia\Model\Order;
use Thelia\TaxEngine\Calculator;

/**
 * Class ApiCall
 * @package ShoppingFlux\EventListeners
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ApiCall implements EventSubscriberInterface
{
    /**
     * Destination class used
     */
    const LOGCLASS = "\\Thelia\\Log\\Destination\\TlogDestinationFile";

    /**
     * @var \Thelia\Log\Tlog
     */
    protected $logger;

    /**
     * @var \Thelia\Model\Customer
     */
    protected $shoppingFluxCustomer;

    /**
     * @var \Thelia\Model\Module
     */
    protected $shoppingFluxPaymentModule;

    /**
     * @var int
     */
    protected $shoppingFluxPaymentModuleId;

    public function __construct()
    {
        $this->logger =  Tlog::getNewInstance();
        $this->logger->setDestinations(static::LOGCLASS);
        $this->logger->setConfig(self::LOGCLASS, 0, THELIA_ROOT . "log" . DS . "log-shopping-flux.txt");

        /**
         * Create a fake user ShoppingFlux if it doesn't exist
         */
        $this->shoppingFluxCustomer = ShoppingFluxConfigQuery::createShoppingFluxCustomer();
        $this->shoppingFluxPaymentModule = ShoppingFluxConfigQuery::createFakePaymentModule();
        $this->shoppingFluxPaymentModuleId = $this->shoppingFluxPaymentModule->getId();
    }


    public function processGetOrders(ApiCallEvent $event)
    {
        $api = $event->getApi();

        $response = $api->getResponse();

        if ($response->isInError()) {
            $this->logger->error($response->getFormattedError());
            throw new BadResponseException($response->getFormattedError());
        }

        $dispatcher = $event->getDispatcher();
        $orders = $response->getGroup("Orders");

        $validOrders = [];

        /** @var \Thelia\Model\Country $country */
        $shopCountry = CountryQuery::create()
            ->findOneByShopCountry(true);

        $calculator = new Calculator();

        /**
         * Check if there is only one order: reformat the array in that case
         */
        if (array_key_exists("IdOrder", $orders["Order"])) {
            $orders = array(
                "Order" => [$orders["Order"]]
            );
        }

        $notImportedOrders = [];

        /**
         * Then treat the orders
         */
        foreach ($orders["Order"] as $orderArray) {
            /**
             * I) create the addresses
             */

            /**
             * Get delivery address, and format empty fields
             */
            $deliveryAddressArray = &$orderArray["ShippingAddress"];
            $deliveryCountryId = CountryQuery::create()->findOneByIsoalpha2(
                strtolower($deliveryAddressArray["Country"])
            )->getId();

            foreach ($deliveryAddressArray as &$value) {
                if (is_array($value)) {
                    $value = "";
                }
            }

            /**
             * Same for invoice address
             */
            $invoiceAddressArray = &$orderArray["BillingAddress"];
            $invoiceCountry = CountryQuery::create()->findOneByIsoalpha2(
                strtolower($invoiceAddressArray["Country"])
            );
            $invoiceCountryId = $invoiceCountry->getId();

            foreach ($invoiceAddressArray as &$value) {
                if (is_array($value)) {
                    $value = "";
                }
            }

            $title = CustomerTitleQuery::create()->findOne()->getId();

            /**
             * Create the order addresses
             */
            $deliveryAddressEvent = new AddressCreateOrUpdateEvent(
                "Delivery address",
                $title,
                $deliveryAddressArray["FirstName"],
                $deliveryAddressArray["LastName"],
                $deliveryAddressArray["Street"],
                $deliveryAddressArray["Street1"],
                $deliveryAddressArray["Street2"],
                $deliveryAddressArray["PostalCode"],
                $deliveryAddressArray["Town"],
                $deliveryCountryId,
                $deliveryAddressArray["PhoneMobile"],
                $deliveryAddressArray["Phone"],
                $deliveryAddressArray["Company"]
            );

            $deliveryAddressEvent->setCustomer(
                $this->shoppingFluxCustomer
            );

            $dispatcher->dispatch(TheliaEvents::ADDRESS_CREATE, $deliveryAddressEvent);


            $invoiceAddressEvent = new AddressCreateOrUpdateEvent(
                "Invoice address",
                $title,
                $invoiceAddressArray["FirstName"],
                $invoiceAddressArray["LastName"],
                $invoiceAddressArray["Street"],
                $invoiceAddressArray["Street1"],
                $invoiceAddressArray["Street2"],
                $invoiceAddressArray["PostalCode"],
                $invoiceAddressArray["Town"],
                $deliveryCountryId,
                $invoiceAddressArray["PhoneMobile"],
                $invoiceAddressArray["Phone"],
                $invoiceAddressArray["Company"]
            );

            $invoiceAddressEvent->setCustomer(
                $this->shoppingFluxCustomer
            );

            $dispatcher->dispatch(TheliaEvents::ADDRESS_CREATE, $invoiceAddressEvent);

            /**
             * II) Add the products to a cart
             */

            /**
             * Format the products array
             */
            if ($orderArray["NumberOfProducts"] == "1") {
                $orderArray["Products"] = array(
                    "Product" => [$orderArray["Products"]["Product"]]
                );
            }

            $productsArray = &$orderArray["Products"]["Product"];

            /**
             * Create a fake cart
             */
            $cart = new Cart();

            /**
             * And fulfil it with the products
             */
            foreach ($productsArray as  &$productArray) {
                $ids = explode("_", $productArray["SKU"]);
                $cartPse = ProductSaleElementsQuery::create()
                    ->findPk($ids[1]);

                $calculator->load($cartPse->getProduct(), $shopCountry);
                $price = $calculator->getUntaxedPrice((float) $productArray["Price"]);

                $cart->addCartItem(
                    (new CartItem())
                        ->setProductSaleElements($cartPse)
                        ->setProduct($cartPse->getProduct())
                        ->setQuantity($productArray["Quantity"])
                        ->setPrice($price)
                        ->setPromoPrice(0)
                        ->setPromo(0)

                );
            }

            /**
             * III) Create/Save the order
             */

            /**
             * Construct order model
             */
            $lang = LangQuery::create()->findOneByLocale($invoiceCountry->getLocale());
            $currency = CurrencyQuery::create()->findOneByCode("EUR");

            $order = OrderQuery::create()
                ->findOneByRef($orderArray["IdOrder"]);

            if ($order !== null) {
                $order->delete();
            }

            $order = new Order();

            $order
                ->setPostage($orderArray["TotalShipping"])
                ->setChoosenDeliveryAddress(
                    $deliveryAddressEvent->getAddress()->getId()
                )
                ->setChoosenInvoiceAddress(
                    $invoiceAddressEvent->getAddress()->getId()
                )
                ->setDeliveryModuleId(
                    ShoppingFluxConfigQuery::getDeliveryModuleId()
                )
                ->setPaymentModuleId($this->shoppingFluxPaymentModuleId)
                ->setTransactionRef($orderArray["Marketplace"])
            ;

            /**
             * Construct event
             */
            $orderEvent = new OrderManualEvent(
                $order,
                $currency,
                $lang,
                $cart,
                $this->shoppingFluxCustomer
            );

            $orderEvent->setDispatcher($dispatcher);

            $dispatcher->dispatch(TheliaEvents::ORDER_CREATE_MANUAL, $orderEvent);

            $placedOrder = $orderEvent->getPlacedOrder();
            $placedOrder
                ->setRef($orderArray["IdOrder"])
                ->setPaid();

            $validOrders []= [
                "IdOrder" => $placedOrder->getRef(),
                "Marketplace" => $placedOrder->getTransactionRef()
            ];
        }

        /**
         * IV) Valid the orders to Shopping Flux
         */

        $request = new Request("ValidOrders");

        foreach ($validOrders as $validOrder) {
            $request->addOrder($validOrder);
        }

        $validOrdersApi = new ValidOrders(
            $response->getToken(),
            $response->getMode()
        );

        $validOrdersApi->setRequest($request);

        $validOrdersResponse = $validOrdersApi->getResponse();

        if ($validOrdersResponse->isInError()) {
            $this->logger
                ->error($response->getFormattedError())
            ;
        }
    }

    public function processUpdateOrders(OrderEvent $event)
    {
        if ($event->getOrder()->getPaymentModuleId() == $this->shoppingFluxPaymentModuleId) {
            $status = $event->getStatus();

            $allowedStatus = [
                    $sentStatusId = OrderStatusQuery::getSentStatus()->getId(),
                    OrderStatusQuery::getCancelledStatus()->getId(),
            ];

            if (in_array($status, $allowedStatus)) {
                $order = $event->getOrder();

                $mode = ShoppingFluxConfigQuery::getProd() ?
                    UpdateOrders::REQUEST_MODE_PRODUCTION :
                    UpdateOrders::REQUEST_MODE_SANDBOX
                ;

                $api = new UpdateOrders(ShoppingFluxConfigQuery::getToken(), $mode);

                $request = new Request("UpdateOrders");
                $request->addOrder(
                    [
                        "IdOrder" => $order->getRef(),
                        "Marketplace" => $order->getTransactionRef(),
                        "Status" => $status === $sentStatusId ? "Shipped" : "Canceled"
                    ]
                );

                $response = $api->setRequest($request)
                    ->getResponse();

                if ($response->isInError()) {
                    $this->logger
                        ->error($response->getFormattedError())
                    ;
                }

                if (!$api->compareResponseRequest()) {
                    $errorMessage = "Bad response from ShoppingFlux: ".
                        $response->getGroup("UpdateOrders")
                            ->C14N()
                    ;

                    $this->logger
                        ->error($errorMessage);
                }
            }
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            ShoppingFluxEvents::GET_ORDERS_EVENT => array("processGetOrders", 128),
            TheliaEvents::ORDER_UPDATE_STATUS => array("processUpdateOrders", 128),

        );
    }

}
