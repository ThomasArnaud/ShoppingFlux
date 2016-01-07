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

use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\Base\TaxQuery;
use Thelia\Model\Cart;
use Thelia\Model\CartItem;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\CountryQuery;
use Thelia\Model\Currency;
use Thelia\Model\ModuleQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\Tax;
use Thelia\Model\TaxRuleCountryQuery;
use Thelia\Module\Exception\DeliveryException;
use Thelia\Tools\URL;

/**
 * Class XMLExportProducts
 * @package ShoppingFlux\Export
 * @author Benjamin Perche <bperche@openstudio.fr>
 * @author Thomas Arnaud <tarnaud@openstudio.fr>
 */
class XMLExportProducts
{
    /**
     * @var string
     *
     * The root tag name
     */
    protected $root = "products";

    protected $locale;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $xml;

    public function __construct(ContainerInterface $containerInterface, $locale = "en_US", $root = null)
    {
        if ($root !== null) {
            $this->root = $root;
        }

        $this->xml = new EscapeSimpleXMLElement("<{$this->root}></{$this->root}>");
        $this->locale = $locale;
        $this->container = $containerInterface;
    }

    /**
     * @return string
     */
    public function doExport()
    {
        /**
         * Define the cache
         */
        $cache = [];

        $cache["brand"] = [];
        $cache["category"] = [];
        $cache["breadcrumb"] = [];
        $cache["feature"]["title"] = [];
        $cache["feature"]["value"] = [];
        $cache["attribute"]["title"] = [];
        $cache["attribute"]["value"] = [];

        $fakeCartItem = new CartItem();
        $fakeCart = new Cart();
        $fakeCart->addCartItem($fakeCartItem);

        /** @var \Thelia\Model\Country $country */
        $country = CountryQuery::create()->findOneById(ConfigQuery::create()->read('store_country', null));

        $deliveryModuleModelId = ShoppingFluxConfigQuery::getDeliveryModuleId();
        $deliveryModuleModel = ModuleQuery::create()->findPk($deliveryModuleModelId);

        /** @var \Thelia\Module\AbstractDeliveryModule $deliveryModule */
        $deliveryModule = $deliveryModuleModel->getModuleInstance($this->container);

        /**
         * Build fake Request to inject in the module
         */
        $fakeRequest = new Request();
        $fakeRequest->setSession(
            (new FakeSession())->setCart($fakeCart)
        );

        $deliveryModule->setRequest($fakeRequest);

        /**
         * Currency
         */
        $currency = Currency::getDefaultCurrency();

        /**
         * Load ecotax
         */
        $ecotax = TaxQuery::create()
            ->findPk(ShoppingFluxConfigQuery::getEcotaxRuleId());

        /**
         * If there's a problem in the configuration, load a fake tax
         */
        if ($ecotax === null) {
            $ecotax = new Tax();
            $ecotax->setType("Thelia\\TaxEngine\\TaxType\\FixAmoutTaxType");
            $ecotax->setRequirements(
                base64_encode(
                    json_encode(
                        ["amount" => 0]
                    )
                )
            );
        }

        /**
         * Load the tax instance
         */
        $ecotaxInstance = $ecotax->getTypeInstance();

        // Compatibility with Thelia <= 2.0.2
        $ecotaxInstance->loadRequirements($ecotax->getRequirements());

        // We can pass any product as Argument, it is not used
        $ecotax = $ecotaxInstance->fixAmountRetriever(new Product());

        /** @var \Thelia\Model\Product $product */
        foreach ($this->getData() as $product) {
            $product->setLocale($this->locale);

            $node = $this->xml->addChild("produit");

            /**
             * Parent id
             */
            $node->addChild("id", $product->getId());
            $node->addChild("nom", $product->getTitle());
            $node->addChild(
                "url",
                URL::getInstance()->absoluteUrl(
                    "/",
                    [
                        "view" => "product",
                        "product_id" => $product->getId(),
                    ]
                )
            );
            $node->addChild("description-courte", $product->getChapo());
            $node->addChild("description", $product->getDescription());

            /**
             * Images URL
             */
            $imagesNode = $node->addChild("images");

            /** @var \Thelia\Model\ProductImage $productImage */
            foreach ($product->getProductImages() as $productImage) {
                $imagesNode->addChild(
                    "image",
                    URL::getInstance()->absoluteUrl(
                        "/shoppingflux/image/" . $productImage->getId()
                    )
                );
            }

            /**
             * Product Brand
             */
            $brand = $product->getBrand();
            $brand->setLocale($this->locale);
            if (!array_key_exists($brandId = $brand->getId(), $cache["brand"])) {
                $cache["brand"][$brandId] = $brand->getTitle();
            }
            $node->addChild("marque", $cache["brand"][$brandId]);
            $node->addChild("url-marque");

            /**
             * Compute breadcrumb
             */
            $category = $product->getCategories()[0];

            if (!array_key_exists($categoryId = $category->getId(), $cache["category"])) {
                $cache["category"][$categoryId] = $category->getTitle();

                $breadcrumb = [];

                do {
                    $category->setLocale($this->locale);
                    $breadcrumb[] = $category->getTitle();
                } while (null !== $category = CategoryQuery::create()->findPk($category->getParent()));

                $reversedBreadcrumb = array_reverse($breadcrumb);

                $reversedBreadcrumb[] = $product->getTitle();

                $cache["breadcrumb"][$categoryId] = implode(" > ", $reversedBreadcrumb);
            }

            $node->addChild("rayon", $cache["category"][$categoryId]);
            $node->addChild("fil-ariane", $cache["breadcrumb"][$categoryId]);

            /**
             * Features
             */
            $featuresNode = $node->addChild("caracteristiques");

            foreach ($product->getFeatureProducts() as $featureProduct) {
                if ($featureProduct->getFeatureAv() !== null &&
                    $featureProduct->getFeature() !== null
                ) {
                    if (!array_key_exists(
                        $featureId = $featureProduct->getFeature()->getId(),
                        $cache["feature"]["title"]
                    )) {
                        $featureProduct->getFeatureAv()->setLocale($this->locale);
                        $featureProduct->getFeature()->setLocale($this->locale);

                        $cache["feature"]["title"][$featureId] = trim(
                            preg_replace(
                                "#[^a-z0-9_\-]#i",
                                "_",
                                $featureProduct->getFeature()->getTitle()
                            ),
                            "_"
                        );

                        $cache["feature"]["value"][$featureId] = $featureProduct->getFeatureAv()->getTitle();
                    }

                    $featuresNode->addChild(
                        $cache["feature"]["title"][$featureId],
                        $cache["feature"]["value"][$featureId]
                    );
                }
            }

            /**
             * Compute VAT
             */
            $taxRuleCountry = TaxRuleCountryQuery::create()
                ->filterByTaxRule($product->getTaxRule())
                ->findOne();

            $tax = $taxRuleCountry->getTax();

            /** @var \Thelia\TaxEngine\TaxType\PricePercentTaxType $taxType*/
            $taxType = $tax->getTypeInstance();

            if (array_key_exists("percent", $taxRequirements = $taxType->getRequirements())) {
                $node->addChild("tva", $taxRequirements["percent"]);
            }

            /**
             * Compute product sale elements
             */
            $productSaleElements =  $product->getProductSaleElementss();

            $psesNode = $node->addChild("declinaisons");

            /** @var \Thelia\Model\ProductSaleElements $pse */
            foreach ($productSaleElements as $pse) {
                /**
                 * Fake the cart so that module::getPostage() returns the price
                 * for only one object
                 */
                $fakeCartItem->setProductSaleElements($pse);

                /**
                 * If the object is too heavy, don't export it
                 */
                try {
                    $shipping_price = $deliveryModule->getPostage($country);
                } catch (DeliveryException $e) {
                    continue;
                }

                $productPrice = $pse->getPricesByCurrency($currency);
                $pse->setVirtualColumn("price_PRICE", $productPrice->getPrice());
                $pse->setVirtualColumn("price_PROMO_PRICE", $productPrice->getPromoPrice());

                $deliveryTimeMin = null;
                $deliveryTimeMax = null;

                $pseNode = $psesNode->addChild("declinaison");

                /**
                 * Child id
                 */
                $pseNode->addChild("id", $product->getId()."_".$pse->getId());

                $pseNode->addChild(
                    "prix-ttc",
                    $pse->getPromo() ? $pse->getPromoPrice() : $pse->getPrice()
                );

                $pseNode->addChild("prix-ttc-barre", $pse->getPromo() ? $pse->getPrice() : null);
                $pseNode->addChild("quantite", $pse->getQuantity());
                $pseNode->addChild("ean", $pse->getEanCode());
                $pseNode->addChild("poids", $pse->getWeight());
                $pseNode->addChild("ecotaxe", $ecotax);
                $pseNode->addChild("frais-de-port", $shipping_price);
                $pseNode->addChild("delai-livraison-mini", $deliveryTimeMin);
                $pseNode->addChild("delai-livraison-maxi", $deliveryTimeMax);

                $pseAttrNode = $pseNode->addChild("attributs");

                /** @var \Thelia\Model\AttributeCombination $attr */
                foreach ($pse->getAttributeCombinations() as $attr) {
                    if ($attr->getAttribute() !== null && $attr->getAttributeAv() !== null) {
                        if (!array_key_exists(
                            $attributeId = $attr->getAttribute()->getId(),
                            $cache["attribute"]["title"]
                        )) {
                            $attr->getAttribute()->setLocale($this->locale);
                            $attr->getAttributeAv()->setLocale($this->locale);

                            $cache["attribute"]["title"][$attributeId] = trim(
                                preg_replace(
                                    "#[^a-z0-9_\-]#i",
                                    "_",
                                    $attr->getAttribute()->getTitle()
                                ),
                                "_"
                            );

                            $cache["attribute"]["value"][$attributeId] = $attr->getAttributeAv()->getTitle();

                        }

                        $pseAttrNode->addChild(
                            $cache["attribute"]["title"][$attributeId],
                            $cache["attribute"]["value"][$attributeId]
                        );
                    }
                }

                $pseNode->addChild("promo-de");
                $pseNode->addChild("promo-a");
            }
        }

        /**
         * Then return a well formed string
         */
        $dom = new \DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->xml->asXML());

        return $dom->saveXML();
    }

    protected function getData()
    {
        $query = ProductQuery::create();

        return $query->find();
    }
}
