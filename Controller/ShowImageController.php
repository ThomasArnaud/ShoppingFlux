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

namespace ShoppingFlux\Controller;
use ShoppingFlux\ShoppingFlux;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Translation\Translator;
use Thelia\Model\ProductImageQuery;

/**
 * Class ShowImageController
 * @package ShoppingFlux\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ShowImageController extends BaseFrontController
{
    public function getImage($imageId)
    {
        $productImage = ProductImageQuery::create()
            ->findPk($imageId)
        ;

        if($productImage === null) {
            throw new FileNotFoundException(
                Translator::getInstance()->trans(
                    "The image id %id doesn't exist",
                    [
                        "%id" => $imageId
                    ],
                    ShoppingFlux::MESSAGE_DOMAIN
                )
            );
        }

        $path = THELIA_LOCAL_DIR . "/media/images/product/" . $productImage->getFile();

        if (!is_file($path) || !is_readable($path)) {
            throw new \ErrorException(
                Translator::getInstance()->trans(
                    "The file %file is not readable",
                    [
                        "%file" => $productImage->getFile()
                    ],
                    ShoppingFlux::MESSAGE_DOMAIN
                )
            );
        }

        $data = file_get_contents($path);

        $mime = MimeTypeGuesser::getInstance()
            ->guess($path)
        ;

        return new Response($data, 200, ["Content-Type"=>$mime]);
    }
} 