<!DOCTYPE HTML>
<html lang='pt-BR'>
    <head>
        <title>Partido Route</title>
    </head>
    <body>
        <?php
            require_once 'Partido.php';
            header('Content-Type: text/html; charset=utf-8');
            require_once '../libs/vendor/autoload.php';
            $app = new \Slim\Slim();

            $app->get('/getPartidos/', function() {
                $partido = new Partido();
                $response = array();
                $result = $partido->getPartidos();

                $response["error"] = false;
                $response["partidos"] = array();

                // looping through result and preparing tasks array
                while ($partidos = $result->fetch_assoc()) {
                    $tmp = array();
                    $tmp["idPartido"] = $partidos["idPartido"];
                    $tmp["nome"] = $partidos["nome"];
                    array_push($response["partidos"], $tmp);
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
    </body>
</html>
