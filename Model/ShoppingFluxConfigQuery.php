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
use ShoppingFlux\Export\XMLExportProducts;
use ShoppingFlux\ShoppingFlux;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Thelia\Core\Translation\Translator;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Customer;
use Thelia\Model\CustomerQuery;
use Thelia\Model\CustomerTitleQuery;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;

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

        $module = ModuleQuery::create()
            ->findPk($id);

        return $module === null ? 0 : $module->getId();
    }

    public static function setDeliveryModule($moduleId)
    {
        ConfigQuery::write("shopping_flux_delivery_module_id", $moduleId);
    }

    public static function getDefaultLangId()
    {
        $id = ConfigQuery::read("shopping_flux_lang_id");

        $lang = LangQuery::create()
            ->findPk($id);

        return $lang === null ? 0 : $lang;
    }

    public static function setDefaultLang($langId)
    {
        ConfigQuery::write("shopping_flux_lang_id", $langId);
    }

    public static function getEcotaxRuleId()
    {
        return ConfigQuery::read("shopping_flux_ecotax_id");
    }

    public static function setEcotaxRule($taxId)
    {
        ConfigQuery::write("shopping_flux_ecotax_id", $taxId);
    }

    /**
     * @return Customer
     */
    public static function createShoppingFluxCustomer()
    {
        $shoppingFluxCustomer = CustomerQuery::create()
            ->findOneByRef("ShoppingFlux");

        if (null === $shoppingFluxCustomer) {
            $shoppingFluxCustomer = new Customer();

            $shoppingFluxCustomer
                ->setRef("ShoppingFlux")
                ->setCustomerTitle(CustomerTitleQuery::create()->findOne())
                ->setLastname("ShoppingFlux")
                ->setFirstname("ShoppingFlux")
                ->save()
            ;
        }

        return $shoppingFluxCustomer;
    }

    public static function exportXML(ContainerInterface $container)
    {

        $lang = LangQuery::create()
            ->findOneById(static::getDefaultLangId());

        if ($lang === null) {
            $lang = Lang::getDefaultLanguage();
        }

        $locale = $lang->getLocale();

        $export = (new XMLExportProducts($container, $locale))->doExport();

        return $export;
    }
}
