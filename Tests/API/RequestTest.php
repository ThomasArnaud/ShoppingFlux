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
use ShoppingFlux\API\Request;
use ShoppingFlux\API\Resource\MarketPlace;
use ShoppingFlux\API\UpdateOrders;

/**
 * Class RequestTest
 * @package ShoppingFlux\Tests\API
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateOrders
     */
    protected $webservice;
    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        $this->webservice = new UpdateOrders();
        $this->request = new Request("UpdateOrders");

        $this->webservice->setRequest($this->request);
    }

    /**
     * The request MUST not be valid if empty
     */
    public function testVoidRequestValidation()
    {
        $this->assertFalse(
            $this->request->isValid(
                $this->webservice->getValidationSchema()
            )
        );
    }

    public function testValidRequestValidation()
    {
        $this->request
            ->addOrder([
                "IdOrder" => "12345",
                "Marketplace" => MarketPlace::AMAZON,
                "Status" => "Canceled",
            ]);

        $this->assertTrue(
            $this->request->isValid(
                $this->webservice->getValidationSchema()
            )
        );
    }

    public function testExport()
    {

        $this->request
            ->addOrder([
                "IdOrder" => "12345",
                "Marketplace" => MarketPlace::AMAZON,
                "Status" => "Canceled",
            ]);

        $this->assertEquals(
            "<UpdateOrders><Order><IdOrder>12345</IdOrder><Marketplace>Amazon</Marketplace><Status>Canceled</Status></Order></UpdateOrders>",
            (string)$this->request
        );
    }
} 