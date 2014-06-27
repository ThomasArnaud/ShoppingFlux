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
use ShoppingFlux\API\AbstractWebService;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ApiCallEvent
 * @package ShoppingFlux\Event
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ApiCallEvent extends Event
{
    protected $api;

    public function __construct(AbstractWebService $api)
    {
        $this->setApi($api);

    }

    /**
     * @param  AbstractWebService $api
     * @return $this
     */
    public function setApi(AbstractWebService $api)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * @return AbstractWebService
     */
    public function getApi()
    {
        return $this->api;
    }

}
