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

use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use ShoppingFlux\ShoppingFlux;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Translation\Translator;
use Thelia\Log\Tlog;

/**
 * Class GetExportController
 * @package ShoppingFlux\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class GetExportController extends BaseFrontController
{
    const SHOPPING_FLUX_CACHE_DIR = "shopping_flux_cache";

    const LOG_CLASS = "\\Thelia\\Log\\Destination\\TlogDestinationFile";

    const CACHE_FILE_NAME = "export.xml";

    const CACHE_TIME_HOUR = 1;

    public function getExport($generateOnly = false)
    {
        $env = $this->container->getParameter("kernel.environment");
        $theliaCacheDirectory = THELIA_CACHE_DIR . $env . DS;

        $generateFile = true;
        $writeCache = false;

        /**
         * Error logging tools
         */
        $logger =  Tlog::getNewInstance();
        $logger->setDestinations(static::LOG_CLASS);
        $logger->setConfig(self::LOG_CLASS, 0, THELIA_ROOT . "log" . DS . "log-shopping-flux.txt");
        $translator = Translator::getInstance();

        $cacheDirectory = $theliaCacheDirectory . static::SHOPPING_FLUX_CACHE_DIR . DS;
        $cacheFile = $cacheDirectory . static::CACHE_FILE_NAME;

        /**
         * Check if the file exists, if it is readable and if
         * we have to get the cache or save it.
         */
        if (file_exists($cacheFile)) {
            if (!is_readable($cacheFile)) {
                $logger->warning(
                    $translator->trans(
                        "The file %file is not readable, the cache can't be used",
                        [
                            "%file" => $cacheFile
                        ],
                        ShoppingFlux::MESSAGE_DOMAIN
                    )
                );
            } else {
                $time = @filemtime($cacheFile);

                if (false === $time) {
                    $logger->error(
                        $translator->trans(
                            "Unknown error while getting %file update time",
                            [
                                "%file" => $cacheFile
                            ],
                            ShoppingFlux::MESSAGE_DOMAIN
                        )
                    );
                } elseif ($time < $limitTime = (time() - static::CACHE_TIME_HOUR * 3600)) {
                    /**
                     * If the cache is too old
                     */
                    if (!is_writable($cacheFile)) {
                        $logger->warning(
                            $translator->trans(
                                "The file %file is not writable, the cache can't be saved",
                                [
                                    "%file" => $cacheFile
                                ],
                                ShoppingFlux::MESSAGE_DOMAIN
                            )
                        );

                    } else {
                        $writeCache = true;
                    }
                } elseif ($time >= $limitTime) {
                    $generateFile = false;
                }
            }
        } else {
            /**
             * Check if the cache directory exists,
             * if not, create it.
             */
            if (!file_exists($cacheDirectory)) {
                if (!@mkdir($cacheDirectory)) {
                    $logger->warning(
                        $translator->trans(
                            "Unable to create the cache directory %dir",
                            [
                                "%dir" => $cacheDirectory
                            ],
                            ShoppingFlux::MESSAGE_DOMAIN
                        )
                    );
                } else {
                    $writeCache = true;
                }
            }
        }

        if (is_file($cacheDirectory) && !unlink($cacheDirectory)) {
            $logger->warning(
                $translator->trans(
                    "Unable to create the cache directory, a file named %dir exists and can't be deleted",
                    [
                        "%dir" => $cacheDirectory
                    ],
                    ShoppingFlux::MESSAGE_DOMAIN
                )
            );
        } elseif (is_dir($cacheDirectory)) {
            if (!is_writable($cacheDirectory)) {
                $logger->warning(
                    $translator->trans(
                        "The directory %dir is not writable, the cache file can't be saved",
                        [
                            "%dir" => $cacheDirectory
                        ],
                        ShoppingFlux::MESSAGE_DOMAIN
                    )
                );
            } elseif (!file_exists($cacheFile)) {
                $writeCache = true;
            }
        }

        /**
         * Then when everything's ok ( directory created if not ) go.
         */
        if ($generateFile) {
            $content = ShoppingFluxConfigQuery::exportXML($this->container);
        } elseif (!$generateOnly) {
            $content = file_get_contents($cacheFile);
        } else {
            $content = null;
        }

        if ($writeCache && $content !== null) {
            file_put_contents($cacheFile, $content);
        }

        if ($generateOnly) {
            return !($generateFile ^ $writeCache) && is_writable($cacheFile);
        }

        return new Response(
            $content,
            200,
            [
                "Content-type" => "application/xml",
            ]
        );
    }
}
