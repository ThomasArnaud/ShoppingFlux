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

namespace ShoppingFlux\Tools;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Thelia\Tools\URL as BaseURL;

/**
 * Class URL
 * @package ShoppingFlux\Tools
 * @author Benjamin Perche <bperche@openstudio.fr>
 *
 * Small hack for command line export to be correct
 */
class URL extends BaseURL
{
    protected $domain;

    public function __construct(ContainerInterface $container, $domain)
    {
        parent::__construct($container);

        parent::$instance = $this;
        $this->domain = $domain;
    }

    public function absoluteUrl($path, array $parameters = null, $path_only = BaseURL::WITH_INDEX_PAGE)
    {
        $generated = parent::absoluteUrl($path, $parameters, $path_only);

        return preg_replace("#(http|https)\://localhost#i", "$1://".$this->domain, $generated);
    }

}
