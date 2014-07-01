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

namespace ShoppingFlux;

use Propel\Runtime\Connection\ConnectionInterface;
use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use Thelia\Module\BaseModule;

class ShoppingFlux extends BaseModule
{
    const MESSAGE_DOMAIN = "shoppingflux";

    public function postActivation(ConnectionInterface $con = null)
    {
        /**
         * Create a fake customer for shopping flux orders
         */
        ShoppingFluxConfigQuery::createShoppingFluxCustomer();

    }

}
