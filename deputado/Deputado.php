<?php
class Deputado{

    private $conn;

    function __construct() {
        require_once '../include/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    function inserirDetalhesDeputado($ideCadastro,$numLegislatura){

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

        $this->inserePartidos($obj);
        $this->insereGabinete($obj);
        $this->insereComissao($obj);
       
        $idPartido = $obj->Deputado->partidoAtual->idPartido;
        $idGabinete = $obj->Deputado->gabinete->numero;
        $ideCadastro = $obj->Deputado->ideCadastro;
        $dataNasc = $obj->Deputado->dataNascimento;
        $nomeProfissao = $obj->Deputado->nomeProfissao;
        $situacaoNaLegislaturaAtual = $obj->Deputado->situacaoNaLegislaturaAtual;

        if ( !is_string($nomeProfissao)){
            $nomeProfissao = NULL;
        }
        $stmt = $this->conn->prepare("UPDATE deputado SET dataNascimento = ? , nomeProfissao= ? , situacaoNaLegislaturaAtual= ? , Gabinete_idGabinete = ? , partido_idPartido = ?  WHERE ideCadastro = ? ");
        $stmt->bind_param( "sssisi", $dataNasc , $nomeProfissao , $situacaoNaLegislaturaAtual, $idGabinete, $idPartido, $ideCadastro);
        $result = $stmt->execute();
        $stmt->close();
        //echo "DEPUTADO ".$obj->Deputado->nomeParlamentarAtual." TEVE OS DADOS ATUALIZADOS <br>";
    }


    function insereGabinete($obj){
        $idGabinete = $obj->Deputado->gabinete->numero;
        $anexo = $obj->Deputado->gabinete->anexo;
        $telefone = $obj->Deputado->gabinete->telefone;
        $stmt = $this->conn->prepare("INSERT INTO gabinete (idGabinete, anexo , telefone) values (? , ? , ?)");
        $stmt->bind_param( "iis", $idGabinete ,$anexo , $telefone);
        $result = $stmt->execute();       
        // ****************** Gabinete Inserido ******************
    }


    function inserePartidos($obj){
        $idPartido = $obj->Deputado->partidoAtual->idPartido;
        $nome = $obj->Deputado->partidoAtual->nome;
        $stmt = $this->conn->prepare("INSERT INTO partido (idPartido, nome) values (? , ?)");
        $stmt->bind_param( "ss", $idPartido , $nome);
        $result = $stmt->execute();      
        // ****************** Partidos inseridos ******************
    }


    function insereComissao($obj){
        $ideCadastro = $obj->Deputado->ideCadastro;
        echo "teste ".$ideCadastro;
        foreach ($obj->Deputado->comissoes as $item) {
            $idOrgao = $item->comissao->idOrgaoLegislativoCD;
            $siglaComissao = $item->comissao->siglaComissao;
            $nomeComissao = $item->comissao->nomeComissao;

            $stmt = $this->conn->prepare("INSERT INTO orgao (idOrgao, siglaComissao, nomeComissao) values (? , ? , ?)");
            $stmt->bind_param( "iss", $idOrgao,$siglaComissao,$nomeComissao);
            $result = $stmt->execute();

            $this->insereDeputadoComissao($ideCadastro,$idOrgao);            
        }

    }

    function insereDeputadoComissao($ideCadastro, $idOrgao){
        $stmt = $this->conn->prepare("INSERT INTO deputado_has_orgao (deputado_ideCadastro,orgao_idOrgao) values (? , ? )");
        $stmt->bind_param( "ii", $ideCadastro,$idOrgao);
        $result = $stmt->execute();
    }


    function obterDeputados(){        

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
            $nomeCivil = $item-> nome;
            $nomeParlamentar = $item-> nomeParlamentar;
            $urlFoto = $item-> urlFoto;
            $sexo = $item-> sexo;
            $ufRepresentacaoAtual = $item-> uf;
            $email = $item-> email;
            $stmt = $this->conn->prepare("INSERT INTO deputado(ideCadastro, matricula, idParlamentar, nomeCivil, nomeParlamentar, urlFoto, sexo, ufRepresentacaoAtual, email) 
                values (? , ? , ? , ? , ? , ? , ? , ? , ? )");
            $stmt->bind_param( "iiissssss", $ideCadastro,$matricula,$idParlamentar,$nomeCivil,$nomeParlamentar,$urlFoto,$sexo,$ufRepresentacaoAtual,$email);
            $result = $stmt->execute();
            $this->inserirDetalhesDeputado($ideCadastro,55);
            /*
            // ----------------- Aqui é um debug caso seja necessário visualizar o que está sendo retornado ----------------------- 

            echo "<img src= '" . $item -> urlFoto ."'/>"."<br />";
            echo "<strong>Ide Cadastro:</strong> ".$item -> ideCadastro."<br />";
            echo "<strong>Nome:</strong> ".$item -> nome."<br />";
            echo "<strong>Nome Parlamentar:</strong> ".$item -> nomeParlamentar."<br />";
            echo "<strong>Sexo:</strong> ".$item -> sexo."<br />";
            echo "<strong>Partido:</strong> ".$item -> partido."<br />";
            echo "<br />";

            */
        }        
        $stmt->close();
    }
}
?>