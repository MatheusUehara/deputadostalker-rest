<!DOCTYPE HTML>
<html lang='pt-BR'>
    <head>
        <title>Deputado Route</title>
    </head>
    <body>
        <?php
            require_once 'Deputado.php';

            header('Content-Type: text/html; charset=utf-8');
            require_once '../libs/vendor/autoload.php';
            $app = new \Slim\Slim();


            /*
            * Primeira rota criada afim de testar os serviços
            */
            $app->get('/:name', function ($name) {
                echo "Hello , $name"."<br>";
            });


            /*
            * Preenche/Atualiza a base de dados com os deputados
            */
            $app->get('/atualizarBaseDeputados/', function () {
                $deputado = new Deputado();
                $deputado->obterDeputadosCamara();
                echo "Base preenchida com sucesso!";
            });

            /*
            * Retorna todos os deputados presentes na base de dados
            */
            $app->get('/getDeputados/', function() {
                $deputado = new Deputado();
                $response = array();
                $result = $deputado->getDeputados();

                $response["error"] = false;
                $response["deputados"] = array();

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
                    array_push($response["deputados"], $tmp);
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
