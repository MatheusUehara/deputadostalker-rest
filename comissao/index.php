<?php
    require_once 'Comissao.php';
    require_once '../libs/vendor/autoload.php';
    $app = new \Slim\Slim();

    $app->get('/comissao/', function() {
        $comissao = new Comissao();
        $result = $comissao->getComissoes();

        $response = array();

        while ($comissoes = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["idOrgao"] = $comissoes["idOrgao"];
            $tmp["siglaComissao"] = $comissoes["siglaComissao"];
            $tmp["nomeComissao"] = $comissoes["nomeComissao"];
            array_push($response, $tmp);
        }

        echoRespnse(200, $response);
    });

$app->get('/comissao/:ideCadastro', function($ideCadastro) {
    $comissao = new Comissao();
    $result = $comissao->getComissoesDeputados();

    $response = array();
    
    while ($comissoes = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["orgao_idOrgao"] = $comissoes["orgao_idOrgao"];
        $tmp["deputado_ideCadastro"] = $comissoes["deputado_ideCadastro"];
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
