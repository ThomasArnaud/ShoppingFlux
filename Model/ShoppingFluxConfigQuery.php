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
use Thelia\Model\LangQuery;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;
use Thelia\Module\AbstractDeliveryModule;

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

    public static function setToken($value)
    {
        ConfigQuery::write("shopping_flux_token", $value);
    }

    /**
     * @return bool
     */
    public static function getProd()
    {
        return @(bool) ConfigQuery::read("shopping_flux_prod");
    }
    public static function setProd($value)
    {
        ConfigQuery::write("shopping_flux_prod", $value);
    }


    public static function getDeliveryModuleId()
    {
        $id = ConfigQuery::read("shopping_flux_delivery_module_id");

        return ModuleQuery::create()
            ->findPk($id);
    }

    public static function setDeliveryModule(Module $module)
    {
        if($module->getType() === AbstractDeliveryModule::DELIVERY_MODULE_TYPE) {
            ConfigQuery::write("shopping_flux_delivery_module_id", $module->getId());
        } else {
            throw new \Exception("The module ".$module->getTitle()." is not a delivery module.");
        }
    }

    public static function getDefaultLangId()
    {
        $id = ConfigQuery::read("shopping_flux_lang_id");

        return LangQuery::create()
            ->findPk($id);
    }


} 