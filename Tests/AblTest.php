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

namespace ShoppingFlux\Tests;


use Thelia\Model\LangQuery;

class AblTest extends \PHPUnit_Framework_TestCase
{
    public function testa() {
        $a = LangQuery::create()
            ->select("Id")
            ->find()
            ->toArray();

        var_dump($a);
    }
}
 