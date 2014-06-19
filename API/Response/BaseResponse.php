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

namespace ShoppingFlux\API\Response;
use ShoppingFlux\API\Exception\BadResponseException;


/**
 * Class BaseResponse
 * @package ShoppingFlux\API\Response
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
abstract class BaseResponse
{
    /**
     * @var string
     *
     * Directory where are the xsd corresponding to XMLs
     */
    protected $resourceFolder;

    /**
     * @var \DOMDocument
     *
     * DOMDocument object to manipulate the response data
     */
    protected $xml;

    /**
     * @var string
     *
     * Raw XML data
     */
    protected $rawData;

    /**
     * @var bool
     *
     * Switch to know if the response is OK or an error
     */
    protected $hasError = false;

    /**
     * @param string $rawData Raw XML data
     *
     * Check if the response is valid, and if it is an error
     */
    public function __construct($rawData)
    {
        $this->resourceFolder = __DIR__ . "/../Resource/";

        // Check if the data is valid
        try {
            $xml = new \DOMDocument("1.0");

            if(!$xml->loadXML($rawData)) {
                throw new \Exception();
            }

            if(!$xml->schemaValidate($this->resourceFolder . $this->getXSDFileName() . "xsd")) {
                if(!$xml->schemaValidate($this->resourceFolder . "ErrorResponse.xsd")) {
                    throw new \Exception();
                }

                $this->hasError = true;
            }

        } catch(\Exception $e) {
            throw new BadResponseException("The response is not a valid Shopping Flux response");
        }

        $this->rawData = $rawData;
        $this->xml = $xml;
    }

    public function getError()
    {
        $error = [];

        if ($this->hasError) {
            $error += ["date" => $this->getDate()];
            $error += ["call" => $this->getCall()];

            $type = $this->get("Type");
            $message = $this->get("Message");

            $error += ["type" => $type];
            $error += ["message" => $message];
        }

        return $error;
    }

    public function getDate()
    {
        return $this->get("Date");

    }

    public function getCall()
    {
        return $this->get("Call");
    }

    public function getToken()
    {
        return $this->get("Token");

    }

    public function getMode()
    {
        return $this->get("Mode");
    }

    public function get($key, $index = 0)
    {
        $list = $this->xml->getElementsByTagName($key);

        if ($count = count($list) == 0) {
            return null;
        } elseif ($count <= $index) {
            $index = 0;
        }

        return $list->item($index)->textContent;
    }

    /**
     * @return string
     *
     * This must return the name of the correct XSD file for validate
     * the $rawData in the constructor.
     * By default, it get the child class' name.
     */
    public function getXSDFileName()
    {
        $className = get_class($this);
        $className = explode("\\", $className);

        end($className);
        return current($className);
    }
} 