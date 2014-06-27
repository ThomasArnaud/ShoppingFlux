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
use ShoppingFlux\Export\XMLExportProducts;
use ShoppingFlux\Form\ConfigureForm;
use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use ShoppingFlux\ShoppingFlux;
use Symfony\Component\Form\Form;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\LangQuery;

/**
 * Class SaveExportController
 * @package ShoppingFlux\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class SaveExportController extends BaseAdminController
{
    public function saveOrExport()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ["ShoppingFlux"], AccessManager::UPDATE)) {
            return $response;
        }

        $form = new ConfigureForm($this->getRequest());
        $errorMessage = null;

        try {
            $boundForm = $this->validateForm($form, "post");

            $action = $boundForm->get("action_type")->getData();

            if (true === $msg = $this->save($boundForm)) {
                $this->getParserContext()
                    ->set(
                        "success_message",
                        Translator::getInstance()->trans(
                            "Configuration successfully saved",
                            [],ShoppingFlux::MESSAGE_DOMAIN
                        )
                    );
            } else {
                throw new \Exception(
                    Translator::getInstance()->trans(
                        $msg,
                        [], ShoppingFlux::MESSAGE_DOMAIN
                    )
                );
            }

            if ($action === "export") {
                return $this->export($boundForm);
            }
        } catch (FormValidationException $e) {
            $errorMessage = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        if (null !== $errorMessage) {
            $form->setErrorMessage($errorMessage);

            $this->getParserContext()
                ->addForm($form)
                ->setGeneralError($errorMessage)
            ;
        }

        return $this->render(
            "module-configure",
            ["module_code"   => "ShoppingFlux"]
        );
    }

    protected function save(Form $form)
    {
        try {
            ShoppingFluxConfigQuery::setToken(
                $form->get("token")->getData()
            );

            ShoppingFluxConfigQuery::setDefaultLang(
                $form->get("lang_id")->getData()
            );

            ShoppingFluxConfigQuery::setDeliveryModule(
                $form->get("delivery_module_id")->getData()
            );

            ShoppingFluxConfigQuery::setProd(
                $form->get("prod")->getData()
            );

            ShoppingFluxConfigQuery::setEcotaxRule(
                $form->get("ecotax_id")->getData()
            );
        } catch (\Exception $e) {
            return "An error occured during the recording of the values (".$e->getMessage().")";
        }

        return true;
    }

    protected function export(Form $form)
    {
        $locale = LangQuery::create()
            ->findOneById($form->get("lang_id")->getData())
            ->getLocale();

        return Response::create(
            (new XMLExportProducts($this->container, $locale))->doExport(),
            200,
            [
                "Content-Type" => "application/xml"
            ]
        );
    }
}
