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

namespace ShoppingFlux\Form;

use ShoppingFlux\Model\ShoppingFluxConfigQuery;
use ShoppingFlux\ShoppingFlux;
use Thelia\Form\BaseForm;

/**
 * Class FeedUrlForm
 * @package ShoppingFlux\Form
 * @author Thomas Arnaud <tarnaud@openstudio.fr>
 */
class FeedUrlForm extends BaseForm
{
    protected function buildForm()
    {
        $form = $this->formBuilder;

        $form
            ->add(
                "feed_url",
                "url",
                array(
                    "label" => $this->translator->trans("ShoppingFlux Feed Url", [], ShoppingFlux::MESSAGE_DOMAIN),
                    "label_attr" => [],
                    "data" => ShoppingFluxConfigQuery::getFeedUrl()
                )
            );
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "shopping_flux_feed_url_form";
    }
}
