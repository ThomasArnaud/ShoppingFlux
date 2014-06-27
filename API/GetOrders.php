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
use ShoppingFlux\API\Response\GetOrdersResponse;

/**
 * Class GetOrders
 * @package ShoppingFlux\API
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class GetOrders extends AbstractWebService
{
    /**
     * @param $dataStruct
     * @param $data
     * @return GetOrdersResponse
     */
    public function parseResponse($dataStruct, $data)
    {
        $response = new GetOrdersResponse($data);

        return $response;
    }
}
