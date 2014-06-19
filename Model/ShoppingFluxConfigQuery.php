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

namespace ShoppingFlux\Model;
use Thelia\Model\ConfigQuery;

/**
 * Class ShoppingFluxConfigQuery
 * @package ShoppingFlux\Model
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ShoppingFluxConfigQuery 
{
    /**
     * @return mixed
     */
    public static function getToken()
    {
        return ConfigQuery::read("shopping_flux_token");
    }

    /**
     * @return bool
     */
    public static function getProd()
    {
        return @(bool) ConfigQuery::read("shopping_flux_prod");
    }
} 