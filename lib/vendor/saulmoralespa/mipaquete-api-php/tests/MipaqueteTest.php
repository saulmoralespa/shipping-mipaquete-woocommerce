<?php

use Mipaquete\Client;
use PHPUnit\Framework\TestCase;

class MipaqueteTest extends TestCase
{

    public $mipaquete;

    public function setUp()
    {
        $email = "test@gmail.com";
        $password = "87654321";

        $this->mipaquete = new Client($email, $password);
        //$this->mipaquete->sandboxMode(true);
    }

    public function testAuth()
    {
        $response = $this->mipaquete->getToken();
        $this->assertObjectHasAttribute("token", $response);
    }

    public function testCalculateSendingMessaging()
    {
        $params = [
            "type" => 2, //1 paqueteria, 2 Mensajería
            "origin" => "5aa1bc55b63d79e54e7da753",
            "destiny" => "5aa1bc55b63d79e54e7da753",
            "weight" => 3,
            "declared_value" => 3000,
            "quantity" => 1,
            "special_service" => 2, //0 ninguno, 2 recaudo, 3  para retorno de documento firmado
            "value_collection" => 2000,
            "payment_type" => 1, // 1 pago con saldo, 5 pago con recaudo
            "value_select" => 3, //criterio selección 1 precio, 2 tiempo, 3 servicio
            "delivery" => "5cb0f5fd244fe2796e65f9c"
        ];

        $response = $this->mipaquete->calculateSending($params);
        var_dump($response);

    }

    public function testCalculateSendingPackaging()
    {
        $params = [
            "type" => 1, //1 paqueteria, 2 Mensajería
            "origin" => "5aa1bc46b63d79e54e7da346",
            "destiny" => "5aa1bc46b63d79e54e7da346",
            "width" => 3,
            "height" => 2,
            "large" => 4,
            "weight" => 7,
            "declared_value" => 3000,
            "quantity" => 1,
            "payment_type" => 1, // 1 pago con saldo, 5 pago con recaudo
            "value_select" => 3, //criterio selección 1 precio, 2 tiempo, 3 servicio
            "delivery" => "5cb0f5fd244fe2796e65f9c"
        ];

        $response = $this->mipaquete->calculateSending($params);
        var_dump($response);

    }

    public function testGetListSendings()
    {
        $response = $this->mipaquete->getListSendings(1);
        var_dump($response);
    }

    public function testCancelSendings()
    {
        $id = "5dc431d3cd24f62a991010bd";  //Clave principal del envío
        $response = $this->mipaquete->cancelSending($id);
        var_dump($response);
    }

    public function testGetListTowns()
    {
        $response = $this->mipaquete->getListTowns();
        var_dump($response);
    }

    public function testGetSingleSending()
    {
        $id = "5d5ffcb4fa561b7a086a882d"; //Clave principal del envío
        $response = $this->mipaquete->getSingleSending();
    }

    public function testSendingMessagingType()
    {
        $params = [
            "type" => 2,
            "weight" => 3,
            "declared_value" => 3000,
            "sender" => [
                "name" => "Test",
                "surname" => "Test",
                "phone" => 3546754,
                "cell_phone" => "3009887654",
                "email" => "test@aossas.com",
                "collection_address" => "Calle 2 #54-23",
                "nit" => "10038475643"
            ],
            "receiver" => [
                "name" => "rewrw",
                "surname" => "rewrw",
                "phone" => 4233452,
                "cell_phone" => 3004938245,
                "email" => "receiver@test.com",
                "destination_address"=> "Calle 23 C #23-54"
            ],
            "origin" => "5aa1bc46b63d79e54e7da346",
            "destiny" => "5aa1bc46b63d79e54e7da346",
            "quantity" => 1,
            "comments" => " ",
            "special_service" => 2, //0 ninguno, 2 recaudo, 3  para retorno de documento firmado
            "payment_type" => 5, // 1 pago con saldo, 5 pago con recaudo
            "collection_information" => [
            "bank" => "Bancolombia",
            "type_account" => "A",
            "number_account" => 3004938484,
            "name_beneficiary" => "Test Sender",
            "number_beneficiary" => 3004938484
            ],
            "value_collection" => 2000,
            "delivery" => "5cb0f5fd244fe2796e65f9c",
            "alternative" => 1
        ];

        $response = $this->mipaquete->sendingType($params);

        var_dump($response);
    }

    public function testSendingPackingType()
    {
        $params = [
            "type" => 1,
            "width" => 3,
            "large" => 4,
            "height" => 2,
            "weight" => 7,
            "declared_value" => 3000,
            "sender" => [
                "name" => "Test",
                "surname" => "Test",
                "phone" => 3546754,
                "cell_phone" => "3009887654",
                "email" => "test@aossas.com",
                "collection_address" => "Calle 3 #54-23",
                "nit" => "10038475643"
            ],
            "receiver" => [
                "name" => "rewrw",
                "surname" => "rewrw",
                "phone" => 4232,
                "cell_phone" => 432423,
                "email" => "fdsfds@osd.com",
                "destination_address" => "casdsa"
            ],
            "origin" => "5aa1bc46b63d79e54e7da346",
            "destiny" => "5aa1bc46b63d79e54e7da346",
            "quantity" => 1,
            "comments" => "",
            "payment_type" => 1,  // 1 pago con saldo, 5 pago con recaudo
            "delivery" => "5cb0f5fd244fe2796e65f9c",
            "alternative" => 1
        ];

        $response = $this->mipaquete->sendingType($params);

        var_dump($response);
    }

    public function testGetNoveltiesSending()
    {
        $id = "5d5ffcb4fa561b7a086a882d";
        $response = $this->mipaquete->getNoveltiesSending($id);
        var_dump($response);
    }
}