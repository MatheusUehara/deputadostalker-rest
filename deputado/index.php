<?php
    require_once '../libs/vendor/autoload.php';
    require_once '../include/DbConnect.php';

     
    $app = new \Slim\Slim();
    $app->get('/:name', function ($name) {
        echo "Hello, $name"."<br>";
    });
    $app->run();  

    function obterDetalhesDeputado($ideCadastro,$numLegislatura){
        $curl = curl_init();        
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.camara.gov.br/SitCamaraWS/Deputados.asmx/ObterDetalhesDeputado?ideCadastro='.$ideCadastro.'&numLegislatura='.$numLegislatura,
            CURLOPT_USERAGENT => 'Deputado Stalker postRequest',
        ));

        $resp = curl_exec($curl);
        curl_close($curl);

        $xml_object = simplexml_load_string($resp);

        $json = json_encode(simplexml_load_string($resp));

        $obj = json_decode($json);

        print_r($obj);



    }

    function obterDeputados(){

        $db = new DbConnect();
        $conn = $db->connect();



        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.camara.gov.br/SitCamaraWS/Deputados.asmx/ObterDeputados',
            CURLOPT_USERAGENT => 'Deputado Stalker getRequest'
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        $xml_object = simplexml_load_string($resp);

        $json = json_encode(simplexml_load_string($resp));

        $obj = json_decode($json);


        foreach($obj -> deputado as $item){

            $ideCadastro = $item-> ideCadastro;
            $matricula = $item-> matricula;
            $idParlamentar = $item-> idParlamentar;
            $nomeCivil = utf8_encode($item-> nome);
            $nomeParlamentar = utf8_encode($item-> nomeParlamentar);
            $urlFoto = utf8_encode($item-> urlFoto);
            $sexo = utf8_encode($item-> sexo);
            $ufRepresentacaoAtual = utf8_encode($item-> uf);
            $email = utf8_encode($item-> email);

            $stmt = $conn->prepare("INSERT INTO deputado(ideCadastro, matricula, idParlamentar, nomeCivil, nomeParlamentar, urlFoto, sexo, ufRepresentacaoAtual, email) 
                values (? , ? , ? , ? , ? , ? , ? , ? , ? )");

            $stmt->bind_param( "iiissssss", $ideCadastro,$matricula,$idParlamentar,$nomeCivil,$nomeParlamentar,$urlFoto,$sexo,$ufRepresentacaoAtual,$email);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                echo DEPUTADO_INSERIDO_COM_SUCESSO;
            } else {
                // Failed to create user
                echo DEPUTADO_FALHA_INSERIR;
            }


            /* ----------------- Aqui é um debug caso seja necessário visualizar o que está sendo retornado ----------------------- */
            /*
            echo "<img src= '" . $item -> urlFoto ."'/>"."<br />";
            echo "<strong>Ide Cadastro:</strong> ".$item -> ideCadastro."<br />";
            echo "<strong>Nome:</strong> ".$item -> nome."<br />";
            echo "<strong>Nome Parlamentar:</strong> ".$item -> nomeParlamentar."<br />";
            echo "<strong>Sexo:</strong> ".$item -> sexo."<br />";
            echo "<strong>Partido:</strong> ".$item -> partido."<br />";
            echo "<br />";
            */
        }
        
    }

    obterDeputados();
    //obterDetalhesDeputado(160976,55);
    

?> 