<?php


namespace Mipaquete;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class Client
{
    const API_BASE_URL = "https://ecommerce.mipaquete.com/api/";
    const SANDBOX_API_BASE_URL = "https://ecommerce.test.mipaquete.com/api/";

    protected $_email;
    protected $_password;
    protected static $_sandbox = false;

    public function __construct($email, $password)
    {
        $this->_email  = $email;
        $this->_password = $password;
    }

    public function sandboxMode($status = false)
    {
        if($status)
            self::$_sandbox = true;
    }

    public static function getBaseURL()
    {
        if (self::$_sandbox)
            return self::SANDBOX_API_BASE_URL;
        return self::API_BASE_URL;
    }

    public function client()
    {
        return new GuzzleClient([
            'base_uri' => self::getBaseURL()
        ]);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function getToken()
    {
        try{
            $response = $this->client()->post("auth", [
                'headers' => [
                    "Content-Type" => "application/json"
                ],
                "json" => [
                    "email" => $this->_email,
                    "password" => $this->_password
                ]
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function calculateSending(array $params)
    {
        try{
            $response = $this->client()->post("sendings/calculate", [
                'headers' => [
                    "Authorization" => "{$this->getToken()->token}",
                    "Content-Type" => "application/json"
                ],
                "json" => $params
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param $page
     * @return mixed
     * @throws \Exception
     */
    public function getListSendings($page)
    {
        try{
            $response = $this->client()->get("sendings/page/$page", [
                'headers' => [
                    "Authorization" => "{$this->getToken()->token}"
                ]
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function cancelSending($id)
    {
        try{
            $response = $this->client()->put("sendings/cancel/$id", [
                'headers' => [
                    "Authorization" => "{$this->getToken()->token}"
                ]
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getListTowns()
    {
        try{
            $response = $this->client()->get("sendings/town", [
                'headers' => [
                    "Authorization" => "{$this->getToken()->token}"
                ]
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getSingleSending($id)
    {
        try{
            $response = $this->client()->get("sendings/get/$id", [
                'headers' => [
                    "Authorization" => "{$this->getToken()->token}"
                ]
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function sendingType($params)
    {
        try{
            $response = $this->client()->post("sendings-type", [
                'headers' => [
                    "Authorization" => "{$this->getToken()->token}",
                    "Content-Type" => "application/json"
                ],
                "json" => $params
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getNoveltiesSending($id)
    {
        try{
            $response = $this->client()->get("novelty/$id", [
                'headers' => [
                    "Authorization" => "{$this->getToken()->token}"
                ]
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    public static function responseJson($response)
    {
        return \GuzzleHttp\json_decode(
            $response->getBody()->getContents()
        );
    }
}