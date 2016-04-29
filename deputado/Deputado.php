<?php

class Deputado{

    private $conn;

    function __construct() {
        require_once '../include/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    function inserirDetalhesDeputado($ideCadastro){

        $curl = curl_init();        
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.camara.gov.br/SitCamaraWS/Deputados.asmx/ObterDetalhesDeputado?ideCadastro='.$ideCadastro.'&numLegislatura=55',
            CURLOPT_USERAGENT => 'Deputado Stalker postRequest',
        ));

        $resp = curl_exec($curl);
        curl_close($curl);
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
        $stmt = $this->conn->prepare("UPDATE deputado SET dataNascimento = ? , nomeProfissao= ? , situacaoLegislaturaAtual= ? , gabinete_idGabinete = ? , partido_idPartido = ?  WHERE ideCadastro = ? ");
        $stmt->bind_param("sssisi", $dataNasc,$nomeProfissao,$situacaoNaLegislaturaAtual,$idGabinete,$idPartido,$ideCadastro);
        $stmt->execute();
        $stmt->close();
    }


    function insereGabinete($obj){
        $idGabinete = $obj->Deputado->gabinete->numero;
        $anexo = $obj->Deputado->gabinete->anexo;
        $telefone = $obj->Deputado->gabinete->telefone;
        $stmt = $this->conn->prepare("INSERT INTO gabinete (idGabinete, anexo , telefone) values (? , ? , ?)");
        $stmt->bind_param( "iis", $idGabinete ,$anexo , $telefone);
        $stmt->execute();
        $stmt->close();
    }


    function inserePartidos($obj){
        $idPartido = $obj->Deputado->partidoAtual->idPartido;
        $nome = $obj->Deputado->partidoAtual->nome;
        $stmt = $this->conn->prepare("INSERT INTO partido (idPartido, nome) values (? , ?)");
        $stmt->bind_param( "ss", $idPartido , $nome);
        $stmt->execute();
        $stmt->close();
    }


    function insereComissao($obj){
        $ideCadastro = $obj->Deputado->ideCadastro;        
        try {
            $count = count($obj->Deputado->comissoes->comissao);
            if(is_array($obj->Deputado->comissoes->comissao)){
                for ($i = 0; $i < $count; $i++) {            
                    $idOrgao = $obj->Deputado->comissoes->comissao[$i]->idOrgaoLegislativoCD;
                    $siglaComissao = $obj->Deputado->comissoes->comissao[$i]->siglaComissao;
                    $nomeComissao = $obj->Deputado->comissoes->comissao[$i]->nomeComissao;

                    $stmt = $this->conn->prepare("INSERT INTO orgao (idOrgao, siglaComissao, nomeComissao) values (? , ? , ?)");
                    $stmt->bind_param( "iss", $idOrgao,$siglaComissao,$nomeComissao);
                    $stmt->execute();
                    $stmt->close();

                    $this->insereDeputadoComissao($ideCadastro,$idOrgao);
                }
            }
        } catch (Exception $e) {
            /*
            Aqui podemos executar um debug para saber quais deputados obtiveram falha

            echo "Deputado abaixo não possui comissões <br>";
            print_r($obj);
            echo "<br> ------------------------------------------------------------------------------------------<br>";
            */

        }
        
    }


    function insereDeputadoComissao($ideCadastro, $idOrgao){
        $stmt = $this->conn->prepare("INSERT INTO deputado_has_orgao (deputado_ideCadastro,orgao_idOrgao) values (? , ? )");
        $stmt->bind_param( "ii", $ideCadastro,$idOrgao);
        $stmt->execute();
        $stmt->close();
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
            $stmt->bind_param( "iiissssss", $ideCadastro, $matricula, $idParlamentar, $nomeCivil, $nomeParlamentar, $urlFoto, $sexo, $ufRepresentacaoAtual, $email);
            $stmt->execute();
            $stmt->close();
            $this->inserirDetalhesDeputado($ideCadastro);
        }        
    }
}
?>