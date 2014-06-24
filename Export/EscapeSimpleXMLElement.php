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
use SimpleXMLElement;

/**
 * Class EscapeSimpleXMLElement
 * @package ShoppingFlux\Export
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class EscapeSimpleXMLElement extends \SimpleXMLElement
{
    public function addChild($name, $value = null, $namespace = null)
    {
        return parent::addChild(htmlspecialchars($name), htmlspecialchars($value), $namespace);
    }

} 