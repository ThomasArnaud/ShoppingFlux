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
use ShoppingFlux\API\Exception\InvalidRequestException;
use ShoppingFlux\API\Exception\MissingFileException;

/**
 * Class AbstractRequestWebService
 * @package ShoppingFlux\API
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
abstract class AbstractRequestWebService extends AbstractWebService
{
    /**
     * @var null|Request
     *
     * The request XML content
     */
    protected $request = null;

    /**
     * @var string
     */
    protected $resourceFolder;

    /**
     * @var string XSD Schema
     */
    protected $validationSchema;

    public function __construct($token = null, $mode = self::REQUEST_MODE_SANDBOX)
    {
        parent::__construct($token, $mode);

        $this->resourceFolder = __DIR__ . "/Resource/Request/";

        if (!is_file($file = $this->resourceFolder . $this->getXSDFileName() . ".xsd")
            || !is_readable($file)
        ) {
            throw new MissingFileException("The file ".$file." doesn't exist");
        }

        $this->validationSchema = file_get_contents($file);
    }

    public function compareResponseRequest()
    {
        $requestOrders = $this->request->getOrders();

        $responseOrders = $this->response->getGroup("Orders");

        $responseOrders = $responseOrders["Order"];
        /**
         * Format the array
         */
        if (array_key_exists("IdOrder", $responseOrders)) {
            $responseOrders = [$responseOrders];
        }

        foreach ($responseOrders as &$responseOrder) {
            $status = $responseOrder["StatusUpdated"];
            unset($responseOrder["StatusUpdated"]);
            $responseOrder["Status"] = $status === "True" ? "Sent" : "Canceled";
        }

        return $requestOrders == $responseOrders;
    }

    protected function call()
    {
        if ($this->request === null || !$this->request->isValid($this->validationSchema)) {
            throw new InvalidRequestException("The request is not valid");
        }

        $this->addPostData('REQUEST',(string) $this->request);

        parent::call();
    }

    public function getValidationSchema()
    {
        return $this->validationSchema;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return string
     *
     * This must return the name of the correct XSD file for validate the request
     * By default, it get the child class' name.
     */
    public function getXSDFileName()
    {
        return $this->getFunctionName();
    }

}
