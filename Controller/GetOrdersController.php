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
use ShoppingFlux\API\Exception\BadResponseException;
use ShoppingFlux\API\GetOrders;
use ShoppingFlux\Event\ApiCallEvent;
use ShoppingFlux\Event\ShoppingFluxEvents;
use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use ShoppingFlux\ShoppingFlux;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Translation\Translator;

/**
 * Class GetOrdersController
 * @package ShoppingFlux\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class GetOrdersController extends BaseAdminController
{
    public function getOrders()
    {
        $token = ShoppingFluxConfigQuery::getToken();
        $mode = ShoppingFluxConfigQuery::getProd() ?
            GetOrders::REQUEST_MODE_PRODUCTION :
            GetOrders::REQUEST_MODE_SANDBOX
        ;

        $event = new ApiCallEvent(new GetOrders($token, $mode));

        try {
            $this->getDispatcher()->dispatch(ShoppingFluxEvents::GET_ORDERS_EVENT ,$event);

            $this->getParserContext()
                ->set(
                    "success_message",
                    Translator::getInstance()->trans(
                        "Orders successfully integrated", [], ShoppingFlux::MESSAGE_DOMAIN
                    )
                );
        } catch(BadResponseException $e) {
            $this->getParserContext()
                ->set("error_message", $e->getMessage());
        }

        return $this->render(
            "module-configure",
            ["module_code"   => "ShoppingFlux"]
        );
    }
} 