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

namespace ShoppingFlux\API;
use ShoppingFlux\API\Response\ValidOrdersResponse;

/**
 * Class ValidOrders
 * @package ShoppingFlux\API
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ValidOrders extends AbstractRequestWebService
{
    /**
     * @param $dataStruct
     * @param $data
     * @return array|\DOMNode|Object|string
     */
    protected function parseResponse($dataStruct, $data)
    {
        $response = new ValidOrdersResponse($data);

        if($response->isInError()) {
            $return = $response->getError();
        } else {
            $return = $response->getGroup("Orders", $dataStruct);
        }

        return $return;
    }

} 