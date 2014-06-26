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

namespace ShoppingFlux\Export;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Exception\InvalidCartException;
use Thelia\Model\Cart;
use Thelia\Model\CartQuery;

/**
 * Class FakeSession
 * @package ShoppingFlux\Export
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class FakeSession extends Session
{
    /**
     * @var Cart $cart Cart object used to compute the price
     */
    protected $cart;

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setCart($cart)
    {
        $this->cart = $cart;

        return $this;
    }

    public function getCart()
    {
        return $this->cart;
    }


} 