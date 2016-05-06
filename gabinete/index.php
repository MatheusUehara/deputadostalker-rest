<?php

    require_once 'Gabinete.php';
    require_once '../libs/vendor/autoload.php';
    $app = new \Slim\Slim();

    $app->get('/getGabinetes/', function() {
        $gabinete = new Gabinete();
        $response = array();
        $result = $gabinete->getGabinetes();

        //$response["error"] = false;
        $response["gabinete"] = array();

        while ($gabinetes = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["idGabinete"] = $gabinetes["idGabinete"];
            $tmp["anexo"] = $gabinetes["anexo"];
            $tmp["telefone"] = $gabinetes["telefone"];
            array_push($response["gabinete"], $tmp);
        }

        echoRespnse(200, $response);
    });


    /**
     * Echoing json response to client
     * @param String $status_code Http response code
     * @param Int $response Json response
     */
    function echoRespnse($status_code, $response) {
        $app = \Slim\Slim::getInstance();
        // Http response code
        $app->status($status_code);

        // setting response content type to json
        $app->contentType('application/json');

        echo json_encode($response);
    }

    $app->run();
?>
