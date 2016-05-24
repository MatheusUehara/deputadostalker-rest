<?php
    require_once 'Deputado.php';
    require_once '../libs/vendor/autoload.php';
    $app = new \Slim\Slim();

    /*
    * Preenche/Atualiza a base de dados com os deputados
    */
    $app->get('/atualizarBaseDeputados/', function () {
        $deputado = new Deputado();
        $deputado->obterDeputadosCamara();
        echo "Base preenchida com sucesso!";
    });
    
    
    $app->get('/frequencia/de=:dataInicial/ate=:dataFinal/deputadoMatricula=:matricula', function ($dataInicial,$dataFinal,$matricula) {

        $deputado = new Deputado();

        $dataInicial = str_replace("-", "/", $dataInicial);
        $dataFinal = str_replace("-", "/", $dataFinal);
        

        $response = $deputado->obterPresencaDeputado($dataInicial,$dataFinal,$matricula);
/*      echo $dataInicial;
        echo $dataFinal;
        echo $matricula;*/

        if ($response == "error") {
            echoRespnse(400, $response);
            
        }else{
            echoRespnse(200, $response);
        }

    });

    /*
    * Retorna todos os deputados presentes na base de dados
    */
    
    $app->get('/', function() {
        $deputado = new Deputado();
        $response = array();
        $result = $deputado->getDeputados();
        $response = array();
        while ($deputados = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["ideCadastro"] = $deputados["ideCadastro"];
            $tmp["matricula"] = $deputados["matricula"];
            $tmp["idParlamentar"] = $deputados["idParlamentar"];
            $tmp["nomeCivil"] = $deputados["nomeCivil"];
            $tmp["nomeParlamentar"] = $deputados["nomeParlamentar"];
            $tmp["urlFoto"] = $deputados["urlFoto"];
            $tmp["sexo"] = $deputados["sexo"];
            $tmp["ufRepresentacaoAtual"] = $deputados["ufRepresentacaoAtual"];
            $tmp["email"] = $deputados["email"];
            $tmp["dataNascimento"] = $deputados["dataNascimento"];
            $tmp["nomeProfissao"] = $deputados["nomeProfissao"];
            $tmp["situacaoLegislaturaAtual"] = $deputados["situacaoLegislaturaAtual"];
            $tmp["gabinete_idGabinete"] = $deputados["gabinete_idGabinete"];
            $tmp["partido_idPartido"] = $deputados["partido_idPartido"];
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