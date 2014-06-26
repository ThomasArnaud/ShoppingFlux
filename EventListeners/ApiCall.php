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
use ShoppingFlux\Event\ApiCallEvent;
use ShoppingFlux\Event\ShoppingFluxEvents;
use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Address\AddressCreateOrUpdateEvent;
use Thelia\Core\Event\Order\OrderManualEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Log\Tlog;
use Thelia\Model\LangQuery;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Model\Cart;
use Thelia\Model\CartItem;
use Thelia\Model\CountryQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\CustomerTitleQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderStatusQuery;

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

    public function __construct()
    {
        $this->logger =  Tlog::getNewInstance();
        $this->logger->setDestinations(static::LOGCLASS);
        $this->logger->setConfig(self::LOGCLASS, 0, THELIA_ROOT . "log" . DS . "log-shopping-flux.txt");

        /**
         * Create a fake user ShoppingFlux if it doesn't exist
         */
        $this->shoppingFluxCustomer = ShoppingFluxConfigQuery::createShoppingFluxCustomer();
    }


    public function processGetOrders(ApiCallEvent $event)
    {
        $api = $event->getApi();

        $response = $api->getResponse();

        if ($response->isInError()) {
            $this->logger->error($response->getFormattedError());
            throw new BadResponseException($response->getError());
        }

        $dispatcher = $event->getDispatcher();
        $orders = $response->getGroup("Orders");

        /**
         * Check if there is only one order: reformat the array in that case
         */
        if(array_key_exists("IdOrder", $orders["Order"])) {
            $orders = array(
                "Order" => [$orders["Order"]]
            );
        }

        /**
         * Then treat the orders
         */
        foreach($orders["Order"] as $orderArray) {
            /**
             * I) create the addresses
             */

            /**
             * Get delivery address, and format empty fields
             */
            $deliveryAddressArray = &$orderArray["ShippingAddress"];
            $deliveryCountry = CountryQuery::create()->findOneByIsoalpha2(
                strtolower($deliveryAddressArray["Country"])
            )->getId();

            foreach($deliveryAddressArray as &$value) {
                if(is_array($value)) {
                    $value = "";
                }
            }

            /**
             * Same for invoice address
             */
            $invoiceAddressArray = &$orderArray["BillingAddress"];
            $invoiceCountry = CountryQuery::create()->findOneByIsoalpha2(
                strtolower($invoiceAddressArray["Country"])
            )->getId();

            foreach($invoiceAddressArray as &$value) {
                if(is_array($value)) {
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
                "",
                "",
                $deliveryAddressArray["PostalCode"],
                $deliveryAddressArray["Town"],
                $deliveryCountry,
                "",
                $deliveryAddressArray["Phone"],
                ""
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
                "",
                "",
                $invoiceAddressArray["PostalCode"],
                $invoiceAddressArray["Town"],
                $invoiceCountry,
                "",
                $invoiceAddressArray["Phone"],
                ""
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
            if($orderArray["NumberOfProducts"] == "1") {
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
            foreach($productsArray as  &$productArray) {
                $cart->addCartItem(
                    (new CartItem())
                        ->setProductSaleElements(
                            ProductSaleElementsQuery::create()
                                ->findPk($productArray["SKU"])
                        )
                        ->addQuantity($productArray["Quantity"])
                        ->setPrice((float)$productArray["Price"])
                );
            }

            /**
             * III) Create/Save the order
             */

            /**
             * Construct order model
             */
            $order = new Order();

            $order->setRef($orderArray["IdOrder"])
                ->setPostage($orderArray["TotalShipping"])
                ->setChoosenDeliveryAddress(
                    $deliveryAddressEvent->getAddress()
                )
                ->setChoosenInvoiceAddress(
                    $invoiceAddressEvent->getAddress()
                )
                ->setDeliveryModuleId(
                    ShoppingFluxConfigQuery::getDeliveryModuleId()
                )
                ->setPaid()
            ;

            /**
             * Construct event
             */
            $orderEvent = new OrderManualEvent(
                $order,
                CurrencyQuery::create()->findOneByCode("EUR"),
                LangQuery::create()->findOne(),
                $cart,
                $this->shoppingFluxCustomer
            );

            
            $orderEvent->setDispatcher($dispatcher);

            $dispatcher->dispatch(TheliaEvents::ORDER_CREATE_MANUAL, $orderEvent);
        }
    }

    public function processUpdateOrders()
    {

    }

    public function processValidOrders()
    {

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
            ShoppingFluxEvents::UPDATE_ORDERS_EVENT => array("processUpdateOrders", 128),
            ShoppingFluxEvents::VALID_ORDERS_EVENT => array("processValidOrders", 128),
        );
    }

} 