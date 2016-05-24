<?php
/**
 * Created by PhpStorm.
 * User: igormlgomes
 * Date: 23/05/16
 * Time: 22:11
 */

require_once 'Proposicoes.php';
require_once '../libs/vendor/autoload.php';
$app = new \Slim\Slim();

$app->get('/', function() {
    $proposicao = new Proposicao();
    $response = array();
    $result = $proposicao->getProposicoes();


    $response = array();
    
    // looping through result and preparing tasks array
    while ($proposicao = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["idProposicao"] = $proposicao["idProposicao"];
        $tmp["nome"] = $proposicao["nome"];
        $tmp["numero"] = $proposicao["numero"];
        $tmp["ano"] = $proposicao["ano"];
        $tmp["txtEmenta"] = $proposicao["txtEmenta"];
        $tmp["txtExplicacaoEmenta"] = $proposicao["txtExplicacaoEmenta"];
        $tmp["dataApresentacao"] = $proposicao["dataApresentacao"];
        $tmp["dataUltimoDespacho"] = $proposicao["dataUltimoDespcaho"];
        $tmp["txtUltimoDespacho"] = $proposicao["txtUltimoDespacho"];
        $tmp["orgao_idOrgao"] = $proposicao["orgao_idOrgao"];
        $tmp["situacaoProposicao_idSituacaoProposicao"] = $proposicao["situacaoProposicao_idSituacaoProposicao"];
        $tmp["tipoProposicao_idTipoProposicao"] = $proposicao["tipoProposicao_idTipoProposicao"];
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
