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

/**
 * Class ShoppingFluxEvents
 * @package ShoppingFlux\Event
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
abstract class ShoppingFluxEvents
{
    const GET_ORDERS_EVENT = "shoppingflux.get.orders";
    const VALID_ORDERS_EVENT = "shoppingflux.valid.orders";
    const UPDATE_ORDERS_EVENT = "shoppingflux.update.orders";
}
