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
use Thelia\Model\CountryQuery;
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

    public function __construct($root = null, $locale = "en_US")
    {
        if ($root !== null) {
            $this->root = $root;
        }

        $this->xml = new \SimpleXMLElement("<$root></$root>");
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
            $node->addChild("description", $product->getDescription());
            $node->addChild("description-courte", $product->getChapo());

            // Delai de livraison - check if the module is installed
            // Marque - check if there's one
            // Rayon ??
            $node->addChild("tva");
            //PSE
            $productSaleElements =  $product->getProductSaleElementss();

            if(count($productSaleElements)) {
                $pses_node = $node->addChild("declinaisons");
                /** @var \Thelia\Model\ProductSaleElements $pse */
                foreach($productSaleElements as $pse) {
                    $pse_node = $pses_node->addChild("declinaison");
                    $pse_node->addChild("prix", $pse->getTaxedPrice($country));
                    $pse_node->addChild("prix-barre");
                    $pse_node->addChild("quantite", $pse->getQuantity());
                    $pse_node->addChild("ean", $pse->getEanCode());
                    $pse_node->addChild("poids", $pse->getWeight());
                    $pse_node->addChild("ecotaxe");

                }
            }
        }
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