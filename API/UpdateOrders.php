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
use ShoppingFlux\API\Response\UpdateOrdersResponse;

/**
 * Class UpdateOrders
 * @package ShoppingFlux\API
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class UpdateOrders extends AbstractRequestWebService
{
    /**
     * @param $dataStruct
     * @param $data
     * @return UpdateOrdersResponse
     */
    public function parseResponse($dataStruct, $data)
    {
        $response = new UpdateOrdersResponse($data);

        return $response;
    }

}
