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
use ShoppingFlux\API\Exception\InvalidModeException;
use ShoppingFlux\API\Exception\MissingTokenException;
use ShoppingFlux\API\Request;
use ShoppingFlux\API\Response\BaseResponse;

/**
 * Class AbstractWebService
 * @package ShoppingFlux\API
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
abstract class AbstractWebService
{
    /**
     * This is the webservice url.
     */
    const SERVICE_URL = 'https://clients.shopping-flux.com/webservice/';

    const REQUEST_MODE_SANDBOX = "Sandbox";

    const REQUEST_MODE_PRODUCTION = "Production";

    /**
     * @var null|string
     *
     * The token that ShoppingFlux gives to the merchant
     */
    protected $token = null;

    /**
     * @var string
     *
     * The service call mode, default: Sandbox
     */
    protected $mode;

    /**
     * @var array
     */
    protected $postData = array();

    /**
     * @var null|BaseResponse
     *
     * The response of the webservice
     */
    protected $response = null;

    /**
     * @param null $token
     * @param string $mode
     */
    public function __construct($token = null, $mode = self::REQUEST_MODE_SANDBOX)
    {
        if($mode !== self::REQUEST_MODE_SANDBOX && $mode !== self::REQUEST_MODE_PRODUCTION) {
            throw new InvalidModeException(
                "The mode must be Sandbox or Production"
            );
        }

        $this->mode = $mode;

        $this->setToken($token);
    }

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param bool $forceCall
     * @return BaseResponse
     */
    public function getResponse($dataStruct = BaseResponse::GROUP_STRUCT_ARRAY, $forceCall = false)
    {
        if($this->response === null || @(bool)$forceCall ) {
            $data = $this->call();
            $this->response = $this->parseResponse($dataStruct, $data);
        }

        return $this->response;
    }

    public function setResponse(BaseResponse $response)
    {
        $this->response = $response;

        return $this;
    }

    public function addPostData($name, $value)
    {
        $this->postData[$name] = $value;

        return $this;
    }

    public function addArrayPostData(array $values)
    {
        $this->postData += $values;

        return $this;
    }

    protected function call()
    {
        if(!is_string($this->token) || empty($this->token)) {
            throw new MissingTokenException("You can't send a request without a token");
        }

        $this->addArrayPostData(array(
            'TOKEN' => $this->token,
            'CALL' => $this->getFunctionName(),
            'MODE' => $this->mode,
        ));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, static::SERVICE_URL);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->postData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curlResponse = curl_exec($curl);
        curl_close($curl);

        return $curlResponse;
    }

    /**
     * @return string
     *
     * The name of the function, by default, the same as the class'
     */
    function getFunctionName()
    {
        $className = get_class($this);
        $className = explode("\\", $className);

        end($className);
        return current($className);
    }

    abstract protected function parseResponse($dataStruct, $data);
} 