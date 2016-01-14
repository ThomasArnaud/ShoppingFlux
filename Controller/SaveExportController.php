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

use ShoppingFlux\Form\ConfigureForm;
use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use ShoppingFlux\ShoppingFlux;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;

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
        $export = null;

        try {
            $boundForm = $this->validateForm($form, "post");

            $action = $boundForm->get("action_type")->getData();

            if (true === $msg = $this->save($boundForm)) {
                $this->getParserContext()
                    ->set(
                        "success_message",
                        Translator::getInstance()->trans(
                            "Configuration successfully saved",
                            [],
                            ShoppingFlux::MESSAGE_DOMAIN
                        )
                    );
            } else {
                throw new \Exception(
                    Translator::getInstance()->trans(
                        $msg,
                        [],
                        ShoppingFlux::MESSAGE_DOMAIN
                    )
                );
            }

            if ($action === "export") {
                $generationController = new GetExportController();
                $generationController->setContainer($this->container);

                $export = $generationController->getExport(true);
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
                ->setGeneralError($errorMessage);
        }

        return $this->render(
            "module-configure",
            [
                "module_code"   => "ShoppingFlux",
                "export" => $export === null ?: $export === true ? "success" : "fail",
            ]
        );
    }

    protected function save(Form $form)
    {
        try {
            ShoppingFluxConfigQuery::setToken(
                $form->get("token")->getData()
            );

            ShoppingFluxConfigQuery::setClientLogin(
                $form->get("client_login")->getData()
            );

            ShoppingFluxConfigQuery::setDefaultLangId(
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

    public function updateFeedUrl()
    {
        if (null !== $response = $this->checkAuth(
            AdminResources::MODULE,
            [ShoppingFlux::MESSAGE_DOMAIN],
            AccessManager::UPDATE
        )
        ) {
            return $response;
        }

        $form = $this->createForm('shoppingflux.update.feed');
        $error_message = null;

        try {
            $validateForm = $this->validateForm($form);
            $data = $validateForm->getData();
            var_dump($data["feed_url"]);

            ShoppingFluxConfigQuery::setFeedUrl($data["feed_url"]);

            return RedirectResponse::create(URL::getInstance()->absoluteUrl('/admin/module/ShoppingFlux'));

        } catch (FormValidationException $e) {
            $error_message = $this->createStandardFormValidationErrorMessage($e);
        }

        if (null !== $error_message) {
            $this->setupFormErrorContext(
                'configuration',
                $error_message,
                $form
            );
            $response = $this->render("module-configure", ['module_code' => 'ShoppingFlux']);
        }
        return $response;
    }
}
