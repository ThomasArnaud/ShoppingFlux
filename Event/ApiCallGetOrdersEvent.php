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

namespace ShoppingFlux\Event;
use ShoppingFlux\API\GetOrders;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ApiCallGetOrdersEvent
 * @package ShoppingFlux\Event
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ApiCallGetOrdersEvent extends Event
{
    protected $api;

    public function __construct(GetOrders $api)
    {
        $this->setApi($api);

    }

    /**
     * @param GetOrders $api
     */
    public function setApi($api)
    {
        $this->api = $api;
    }

    /**
     * @return GetOrders
     */
    public function getApi()
    {
        return $this->api;
    }


} 