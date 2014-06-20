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

namespace ShoppingFlux\Tests\API;
use ShoppingFlux\API\GetOrders;
use ShoppingFlux\API\Response\GetOrdersResponse;

/**
 * Class GetOrdersTest
 * @package ShoppingFlux\Tests\API
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class GetOrdersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GetOrders
     */
    protected $webservice;


    public function setUp()
    {
        $this->webservice = new GetOrders("TOKEN", GetOrders::REQUEST_MODE_SANDBOX);
    }

    public function testGetFunctionName()
    {
        $this->assertEquals(
            "GetOrders",
            $this->webservice->getFunctionName()
        );
    }

    /**
     * @expectedException \ShoppingFlux\API\Exception\BadResponseException
     */
    public function testInvalidXMLResponse()
    {
        $rawData = "<foo></bar>";
        $response = new GetOrdersResponse($rawData);
    }

    /**
     * @expectedException \ShoppingFlux\API\Exception\BadResponseException
     */
    public function testEmptyResponse()
    {
        $rawData = "<Result></Result>";
        $response = new GetOrdersResponse($rawData);
    }

    public function testValidResponse()
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

        $this->assertEquals(
            "2011-08-09T18:38:16+02:00",
            $response->getDate()
        );

        $this->assertEquals(
            "GetOrders",
            $response->getCall()
        );

        $this->assertEquals(
            "abcdef0123456789abcdef123456789abcdef123",
            $response->getToken()
        );

        $this->assertEquals(
            GetOrders::REQUEST_MODE_SANDBOX,
            $response->getMode()
        );

        /*var_dump($response->getGroup("Orders"));

        $array = [];
        $this->assertEquals(
            json_encode($array),
            json_encode($response->getGroup("Orders"))
        );*/

    }

}
 