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
use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use ShoppingFlux\ShoppingFlux;
use ShoppingFlux\Tools\URL;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thelia\Command\ContainerAwareCommand;
use Thelia\Core\Translation\Translator;

/**
 * Class UpdateExportXML
 * @package ShoppingFlux\Command
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class UpdateExportXML extends ContainerAwareCommand
{
    /**
     * Set the name and the description of the command
     */
    protected function configure()
    {
        $this
            ->setName("module:shoppingflux:updatexml")
            ->setDescription("Update your catalog export")
            ->addArgument("domain-name", InputArgument::REQUIRED, "Your domain domain (example: www.example.com)")
        ;
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return void
     *
     * Update the export.xml file
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        new URL($this->getContainer(), $input->getArgument("domain-name"));
        $translator = new Translator($this->getContainer());

        ShoppingFluxConfigQuery::exportXML(
            $this->getContainer(),
            ShoppingFluxConfigQuery::getDefaultLangId()
        );

        $output->writeln(
            $translator->trans(
                "The file %file has been correctly written",
                [
                    "%file" => "web/cache/export.xml"
                ],
                ShoppingFlux::MESSAGE_DOMAIN
            )
        );
    }
}
