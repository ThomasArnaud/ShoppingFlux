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
                    <Version>2</Version>
                </Request>
                <Response>
                    <Orders>
                        <Order>
                            <IdOrder>123456820006-123456127010</IdOrder>
                            <Marketplace>eBay</Marketplace>
                            <Currency>EUR</Currency>
                            <TotalAmount>10.99</TotalAmount>
                            <TotalProducts>7.99</TotalProducts>
                            <TotalShipping>3.0</TotalShipping>
                            <TotalFees/>
                            <NumberOfProducts>1</NumberOfProducts>
                            <OrderDate>2011-07-08T15:32:53+02:00</OrderDate>
                            <Other/>
                            <ShippingMethod>Colissimo</ShippingMethod>
                            <BillingAddress>
                                <LastName>Nom</LastName>
                                <FirstName/>
                                <Phone>0123456789</Phone>
                                <PhoneMobile />
                                <Street>1 rue du paradis</Street>
                                <Street1>1 rue du paradis</Street1>
                                <Street2>1 rue du paradis</Street2>
                                <Company>Openstudio</Company>
                                <PostalCode>75000</PostalCode>
                                <Town>Paris</Town>
                                <Country>FR</Country>
                                <Email/>
                            </BillingAddress>
                            <ShippingAddress>
                                <LastName>Nom</LastName>
                                <FirstName/>
                                <Phone>0123456789</Phone>
                                <PhoneMobile />
                                <Street>1 rue du paradis</Street>
                                <Street1>1 rue du paradis</Street1>
                                <Street2>1 rue du paradis</Street2>
                                <Company>Openstudio</Company>
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
                                    <Ecotax>1.0</Ecotax>
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

        /**
         * Good to know: empty tags (like: <email />) preduces an empty array, not a empty string in getGroup
         */
        $expected = [
            "Order" => [
                "IdOrder" => "123456820006-123456127010",
                "Marketplace"=>"eBay",
                "Currency"=>"EUR",
                "TotalAmount"=>"10.99",
                "TotalProducts"=>"7.99",
                "TotalShipping"=>"3.0",
                "TotalFees"=>[],
                "NumberOfProducts"=>"1",
                "OrderDate"=>"2011-07-08T15:32:53+02:00",
                "Other"=>[],
                "ShippingMethod"=>"Colissimo",
                "BillingAddress" => [
                    "LastName"=>"Nom",
                    "FirstName"=>[],
                    "Phone"=>"0123456789",
                    "PhoneMobile"=>[],
                    "Street"=>"1 rue du paradis",
                    "Street1"=>"1 rue du paradis",
                    "Street2"=>"1 rue du paradis",
                    "Company"=>"Openstudio",
                    "PostalCode"=>"75000",
                    "Town"=>"Paris",
                    "Country"=>"FR",
                    "Email"=>[],
                ],
                "ShippingAddress"=> [
                    "LastName"=>"Nom",
                    "FirstName"=>[],
                    "Phone"=>"0123456789",
                    "PhoneMobile"=>[],
                    "Street"=>"1 rue du paradis",
                    "Street1"=>"1 rue du paradis",
                    "Street2"=>"1 rue du paradis",
                    "Company"=>"Openstudio",
                    "PostalCode"=>"75000",
                    "Town"=>"Paris",
                    "Country"=>"FR",
                    "Email"=>[],
                ],
                "Products" => [
                    "Product" => [
                        "SKU"=>"1234",
                        "Quantity"=>"1",
                        "Price"=>"7.99",
                        "Ecotax"=>"1.00",
                    ],
                ],
            ],
        ];

        /** @var array $result */
        $result = $response->getGroup("Orders");

        sort($expected);
        sort($result);

        $this->assertEquals($expected,$result);
    }

}
