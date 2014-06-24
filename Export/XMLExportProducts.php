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
use Thelia\Model\CategoryQuery;
use Thelia\Model\CountryQuery;
use Thelia\Model\FeatureQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\ProductQuery;
use Thelia\Tools\URL;

/**
 * Class XMLExportProducts
 * @package ShoppingFlux\Export
 * @author Benjamin Perche <bperche@openstudio.fr>
 *
 * This class generate a french XML of the product catalog, because ShoppingFlux is french.
 */
class XMLExportProducts
{
    /**
     * @var string
     *
     * The root tag name
     */
    protected $root = "produits";

    protected $locale;

    protected $xml;

    public function __construct($locale = "en_US", $root = null)
    {
        if ($root !== null) {
            $this->root = $root;
        }

        $this->xml = new EscapeSimpleXMLElement("<{$this->root}></{$this->root}>");
        $this->locale = $locale;
    }

    public function doExport()
    {
        /** @var \Thelia\Model\Country $country */
        $country = CountryQuery::create()
            ->findOneByShopCountry(true);

        /** @var \Thelia\Model\Product $product */
        foreach($this->getData() as $product) {
            $product->setLocale($this->locale);

            $node = $this->xml->addChild("produit");

            $node->addChild("id_parent", $product->getId());
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

            // Delivery delay - check if the module is installed
            $deliveryDateModule = ModuleQuery::create()
                ->findOneByCode("DeliveryDate");
            $deliveryDateModuleExists = null !== $deliveryDateModule && $deliveryDateModule->getActivate();

            /**
             * Brand - check if there's one
             * TODO
             */
            $node->addChild("marque");
            $node->addChild("url-marque");

            /**
             * Compute breadcrumb
             */
            $breadcrumb = [];
            $category = $product->getCategories()[0];
            $lastCategory = $category->getTitle();

            do {
                $breadcrumb[] = $category->getTitle();
            } while(null !== $category = CategoryQuery::create()->findPk($category->getParent()));

            $reversedBreadcrumb = array_reverse($breadcrumb);

            $node->addChild("rayon", $lastCategory);
            $node->addChild("fil-ariane", implode(" > ", $reversedBreadcrumb));

            /**
             * Get VAT
             * TODO
             */
            $node->addChild("tva");

            /**
             * Features
             */
            $featuresNode = $node->addChild("caracteristiques");
            foreach ($product->getFeatureProducts() as $featureProduct) {
                $featuresNode->addChild(
                    $featureProduct->getFeature()->getTitle(),
                    $featureProduct->getFeatureAv()->getTitle()
                );
            }

            /**
             * Compute product sale elements
             */
            $productSaleElements =  $product->getProductSaleElementss();

            $psesNode = $node->addChild("declinaisons");

            /** @var \Thelia\Model\ProductSaleElements $pse */
            foreach($productSaleElements as $pse) {
                $deliveryTimeMin = null;
                $deliveryTimeMax = null;

                /**
                 * Handle the delivery time if the module exists
                 */
                if($deliveryDateModuleExists) {
                    $deliveryDate = \DeliveryDate\Model\ProductDateQuery::create()
                        ->findPk($pse->getId());

                    $deliveryTimeMin = $pse->getQuantity() ?
                        $deliveryDate->getDeliveryTimeMin() :
                        $deliveryDate->getRestockTimeMin()
                    ;
                    $deliveryTimeMax = $pse->getQuantity() ?
                        $deliveryDate->getDeliveryTimeMax() :
                        $deliveryDate->getRestockTimeMax()
                    ;
                }

                $pseNode = $psesNode->addChild("declinaison");
                $pseNode->addChild("id_enfant", $pse->getId());
                /**
                 * Get price
                 * TODO
                 */
                //$pse_node->addChild("prix", $pse->getTaxedPrice($country));
                $pseNode->addChild("prix-barre");
                $pseNode->addChild("quantite", $pse->getQuantity());
                $pseNode->addChild("ean", $pse->getEanCode());
                $pseNode->addChild("poids", $pse->getWeight());
                $pseNode->addChild("ecotaxe");
                $pseNode->addChild("delai-livraison-mini",$deliveryTimeMin);
                $pseNode->addChild("delai-livraison-maxi",$deliveryTimeMax);

                $pseAttrNode = $pseNode->addChild("attributs");
                /** @var \Thelia\Model\AttributeCombination $attr */
                foreach($pse->getAttributeCombinations() as $attr) {
                    $pseAttrNode->addChild(
                        $attr->getAttribute()->getTitle(),
                        $attr->getAttributeAv()->getTitle()
                    );
                }

            }

        }
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

    /**
     * <produit>
    <id_parent>2133</id_parent>
    <nom>Nom produit</nom>
    <url>url produit</url>
    <description>abcd (code HTML accepté) </description>
    <description-courte>abcd (code HTML accepté) </description-courte>
    <prix>29.9</prix>
    <prix-barre>35.9</prix-barre>
    <frais-de-port>0</frais-de-port>
    <delai-livraison>2.</delai-livraison>
    <marque>Nom Marque</marque>
    <rayon>Nom rayon</rayon>
    <quantite>1</quantite>
    <ean>3660895461866</ean>
    <poids>0.2</poids>
    <prix-achat>0.2</prix-achat>
    <ecotaxe>0.000000</ecotaxe>
    <tva>19.6</tva>
    <ref-constructeur>L22019Z</ref-constructeur>
    <ref-fournisseur>L22019Z</ref-fournisseur>
    <images>
        <image>URL image1</image>
        <image>URL image2</image>
    </images>
    <url-categories>
        <url> URL category1</url>
        <url> URL category2</url>
    </url-categories>
    <caracteristiques>
        <Cara1>Argent</Cara1>
        <Cara2>1</Cara2>
        <Cara3>Fashion</Cara3>
        <Cara4>Femme</Cara4>
    </caracteristiques>
    <declinaisons>
        <declinaison>
            <id_enfant>342</id_enfant>
            <ean></ean>
            <quantite>1</quantite>
            <prix>29.9</prix>

            <prix-barre>29.9</prix-barre>
            <frais-de-port>0</frais-de-port>
            <images>
                <image>URL image 1 Declinaison</image>
                <image>URL image 2 Declinaison</image>
            </images>
            < !—attributs différents entre chaque déclinaison -->
            <attributs>
                <taille>52</taille>
                <matière>coton</matière>
            </attributs>
        </declinaison>
    </declinaisons>
    <fil-ariane>Accueil > gamme > sous gamme > rayon</fil-ariane>
    <manufacturer>Nom fabricant</manufacturer>
    <supplier> Nom fournisseur </supplier>
    <brand-url> Url Brand </brand-url>
    <discount-from> 2012-09-17 16:01:37 </discount-from>
    <discount-to> 2012-09-27 16:01:37 </discount-to>
    </produit>
     */
} 