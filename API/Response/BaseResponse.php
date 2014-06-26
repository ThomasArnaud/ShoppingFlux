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
     * Those constants are used by getGroup, they define the type of data this method
     * has to return
     */
    const GROUP_STRUCT_ARRAY = "array";

    const GROUP_STRUCT_OBJECT = "object";

    const GROUP_STRUCT_DOM_NODE = "domNode";

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
     * @var array
     *
     * List of the structures available for groups
     */
    protected $structures = array();

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

            try {
                $xml->schemaValidate($this->resourceFolder . $this->getXSDFileName() . ".xsd");
            } catch(\Exception $e) {
                $xml->schemaValidate($this->resourceFolder . "ErrorResponse.xsd");
                $this->hasError = true;
            }

        } catch(\Exception $e) {
            throw new BadResponseException(
                "The response is not a valid Shopping Flux response"
            );
        }

        $this->rawData = $rawData;
        $this->xml = $xml;
        $this->structures = [
            self::GROUP_STRUCT_ARRAY,
            self::GROUP_STRUCT_OBJECT,
            self::GROUP_STRUCT_DOM_NODE
        ];
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

    public function getFormattedError()
    {
        $data = $this->getError();
        $returnString = null;

        if (count($data) === 4) {
            $returnString = "[ShoppingFlux Error ".$data["call"]."][".$data["date"]."] ";
            $returnString .= $data["type"].": ".$data["message"];
        }


        return $returnString;
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

    public function get($key, $index = 0, $textContent = true)
    {
        $list = $this->xml->getElementsByTagName($key);

        if ($count = count($list) == 0) {
            return null;
        } elseif ($count <= $index) {
            $index = 0;
        }

        $item = $list->item($index);

        return ($textContent ? $item->textContent : $item);
    }

    /**
     * @param $key
     * @param string $as
     * @return \DOMNode|array|Object|string
     * @throws \Exception
     */
    public function getGroup($key, $as = self::GROUP_STRUCT_ARRAY)
    {
        switch($as) {
            case self::GROUP_STRUCT_ARRAY:
                return $this->getGroupAsArray($key);

            case self::GROUP_STRUCT_OBJECT:
                return $this->getGroupAsObject($key);

            case self::GROUP_STRUCT_DOM_NODE:
                return $this->getGroupAsDomNode($key);

            default:
                throw new \Exception("The group structure ".$as." isn't available");
        }
    }

    protected function getGroupAsArray($key)
    {
        $item = $this->getGroupAsDomNode($key);

        $xml = new \SimpleXMLElement($item->C14N());
        $arrayXML = json_decode(json_encode($xml), true);

        return $arrayXML;
    }

    protected function getGroupAsObject($key)
    {
        $item = $this->getGroupAsDomNode($key);

        $xml = new \SimpleXMLElement($item->C14N());
        $objectXML = json_decode(json_encode($xml));

        return $objectXML;
    }

    public function isInError()
    {
        return $this->hasError;
    }

    protected function getGroupAsDomNode($key)
    {
        return is_object($node = $this->get($key, 0, false)) ?
            $node :
            (new \DOMDocument("1.0"))->appendChild(
                new \DOMElement("error", "No such group \"$key\"")
            );
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