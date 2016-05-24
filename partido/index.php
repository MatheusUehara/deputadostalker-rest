<?php
    require_once 'Partido.php';
    require_once '../libs/vendor/autoload.php';
    $app = new \Slim\Slim();

    $app->get('/', function() {
        $partido = new Partido();
        $response = array();
        $result = $partido->getPartidos();

        
        $response = array();

        // looping through result and preparing tasks array
        while ($partidos = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["idPartido"] = $partidos["idPartido"];
            $tmp["nome"] = $partidos["nome"];
            array_push($response, $tmp);
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
        $app->contentType('json');

        echo json_encode($response);
    }
    $app->run();
?>
