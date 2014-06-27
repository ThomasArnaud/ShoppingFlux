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

namespace ShoppingFlux\Command;
use ShoppingFlux\API\GetOrders;
use ShoppingFlux\Event\ApiCallEvent;
use ShoppingFlux\Event\ShoppingFluxEvents;
use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use Thelia\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GetOrdersCommand
 * @package ShoppingFlux\Command
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class GetOrdersCommand extends ContainerAwareCommand
{
    /**
     * Set the name and the description of the command
     */
    protected function configure()
    {
        $this
            ->setName("module:shoppingflux:getorders")
            ->setDescription("Get the Shopping Flux module orders")
        ;
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return void
     *
     * Create a new
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = ShoppingFluxConfigQuery::getToken();
        $mode = ShoppingFluxConfigQuery::getProd();

        $txt_mode = $mode ? GetOrders::REQUEST_MODE_PRODUCTION : GetOrders::REQUEST_MODE_SANDBOX;
        $api = new GetOrders($token, $txt_mode);

        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
        $dispatcher = $this
            ->getContainer()
            ->get('event_dispatcher');

         $dispatcher->dispatch(
             ShoppingFluxEvents::GET_ORDERS_EVENT,
             new ApiCallEvent($api)
         );
    }
}
