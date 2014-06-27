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

namespace ShoppingFlux\Tests\EventListener;
use ShoppingFlux\API\GetOrders;
use ShoppingFlux\API\Response\GetOrdersResponse;
use ShoppingFlux\Event\ApiCallEvent;
use ShoppingFlux\EventListeners\ApiCall;

/**
 * Class ApiCallTest
 * @package ShoppingFlux\Tests\EventListener
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ApiCallTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiCall
     */
    protected $listener;

    public function setUp()
    {
        $this->listener = new ApiCall();
    }

    public function testGetOrder()
    {
        $rawData = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
            <Result>
                    <Request>
                    <Date>2011-08-09T18:38:16+02:00</Date>
                    <Call>GetOrders</Call>
                    <Token>abcdef0123456789abcdef123456789abcdef123</Token>
                    <Mode>Sandbox</Mode>
                </Request>
                <Response>
                    <Orders>
                        <Order>
                            <IdOrder>123456820006-123456127010</IdOrder>
                            <Marketplace>eBay</Marketplace>
                            <TotalAmount>10.99</TotalAmount>
                            <TotalProducts>7.99</TotalProducts>
                            <TotalShipping>3.0</TotalShipping>
                            <NumberOfProducts>1</NumberOfProducts>
                            <OrderDate>2011-07-08T15:32:53+02:00</OrderDate>
                            <BillingAddress>
                                <LastName>Nom</LastName>
                                <FirstName/>
                                <Phone>0123456789</Phone>
                                <Street>1 rue du paradis</Street>
                                <PostalCode>75000</PostalCode>
                                <Town>Paris</Town>
                                <Country>FR</Country>
                                <Email/>
                            </BillingAddress>
                            <ShippingAddress>
                                <LastName>Nom</LastName>
                                <FirstName/>
                                <Phone>0123456789</Phone>
                                <Street>1 rue du paradis</Street>
                                <PostalCode>75000</PostalCode>
                                <Town>Paris</Town>
                                <Country>FR</Country>
                                <Email/>
                            </ShippingAddress>
                            <Products>
                                <Product>
                                    <SKU>1234</SKU>
                                    <Quantity>1</Quantity>
                                    <Price>7.99</Price>
                                </Product>
                            </Products>
                        </Order>

                    </Orders>
                </Response>
            </Result>
XML;

        $response = new GetOrdersResponse($rawData);
        $api = new GetOrders("foo");
        $api->setResponse($response);
        $event = new ApiCallEvent($api);
        $event->setDispatcher(
            $this->getMock("Symfony\Component\EventDispatcher\EventDispatcher")
        );

        $this->listener->processGetOrders($event);

    }
}
