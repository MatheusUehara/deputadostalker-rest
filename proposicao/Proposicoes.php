<?php

/**
 * Created by PhpStorm.
 * User: igormlgomes
 * Date: 22/05/16
 * Time: 23:16
 */
class Proposicoes
{
    private $conn;

    function __construct() {
        require_once '../include/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /*
    * Obtem os detalhes da propsição a partir nome do autor.
    */

    function getProposicoesDeputado( $parteNomeAutor ){
        $stmt = $this->conn->prepare("SELECT Id FROM proposicao where parteNomeAutor = ?");
        $stmt ->bind_param('i', $parteNomeAutor);
        $stmt->execute();
        $result = $stmt->get_result();
        $idProposicao = $result->fetch_assoc();

        $stmt->close();
        return $idProposicao['Id'];


    }

    /*
    * Retorna os relacionamentos de todos os deputados e suas proposicoes
    */
    function getProposicoesDeputados(){
        $stmt = $this->conn->prepare("SELECT * FROM deputado_has_proposicao");
        $stmt->execute();
        $deputados = $stmt->get_result();
        $stmt->close();
        return $deputados;
    }


    /*
    * Retorna a lista de proposicoes de um deputado especifico
    */
    function getProposicaoDeputado($ideCadastro){

    }


    /*
    * Cria o relacionamento entre o deputado e a proposicao no banco de dados
    */
    function insereDeputadoProposicao($ideCadastro, $idProposicao){
        $stmt = $this->conn->prepare("INSERT INTO deputado_has_orgao (deputado_ideCadastro,proposicao_idProposicao) values (? , ? )");
        $stmt->bind_param( "ii", $ideCadastro,$idProposicao);
        $stmt->execute();
        $stmt->close();
    }


    /*
     * Retorna todas as proposicoes
     */
    function getProposicoes(){
        $stmt = $this->conn->prepare("SELECT * FROM proposicoes");
        $stmt->execute();
        $proposicoes = $stmt->get_result();
        $stmt->close();
        return $proposicoes;
    }


    /*
    * Adiciona a proposicao no banco de dados
    */
    function insereProposicoes($parteNomeAutor, $ideCadastro){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.camara.gov.br/SitCamaraWS/Proposicoes.asmx/ListarProposicoes?parteNomeAutor='.$parteNomeAutor,
            CURLOPT_USERAGENT => 'Deputado Stalker postRequest',
        ));

        $resp = curl_exec($curl);
        curl_close($curl);
        $json = json_encode(simplexml_load_string($resp));
        $obj = json_decode($json);
        try {
            $count = count($obj->Deputado->proposicoes->proposicao);
            if(is_array($obj->Deputado->proposicoes->proposicao)){
                for ($i = 0; $i < $count; $i++) {
                    $idProposicao = $obj->Deputado->proposicoes->proposicao[$i]->Id;
                    $nome = $obj->Deputado->proposicoes->proposicao[$i]->Nome;
                    $numero = $obj->Deputado->proposicoes->proposicao[$i]->Numero;
                    $ano = $obj->Deputado->proposicoes->proposicao[$i]->Ano;
                    $txtEmenta = $obj->Deputado->proposicoes->proposicao[$i]->Ementa;
                    $txtExplicacaoEmenta = $obj->Deputado->proposicoes->proposicao[$i]->ExplicacaoEmenta;
                    $dataApresentacao = $obj->Deputado->proposicoes->proposicao[$i]->DataApresentacao;
                    $dataUltimoDespacho = $obj->Deputado->proposicoes->proposicao[$i]->UltimoDespacho;
                    $txtUltimoDespacho = $obj->Deputado->proposicoes->proposicao[$i]->UltimoDespacho;
                    $orgao_idOrgao = $obj->Deputado->proposicoes->proposicao[$i]->orgao_idOrgao;
                    $situacaoProposicao_idSituacaoProposicao = $obj->Deputado->proposicoes->proposicao[$i]->situacaoProposicao_idSituacaoProposicao;
                    $tipoProposicao_idTipoProposicao = $obj->Deputado->proposicoes->proposicao[$i]->tipoProposicao_idTipoProposicao;

                    $stmt = $this->conn->prepare("INSERT INTO proposicao (idProposicao, nome, numero, ano, txtEmenta, txtExplicacaoEmenta,
 dataApresentacao, dataUltimoDespacho, txtUltimoDespacho, orgao_idOrgao, situacaoProposicao_idSituacaoProposicao, tipoProposicao_idTipoProposicao ) 
 values (? , ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param( "isissssssiii", $idProposicao,$nome,$numero, $ano, $txtEmenta, $txtExplicacaoEmenta, $dataApresentacao,
                        $dataUltimoDespacho, $txtUltimoDespacho, $orgao_idOrgao, $situacaoProposicao_idSituacaoProposicao, $tipoProposicao_idTipoProposicao);
                    $stmt->execute();
                    $stmt->close();

                    $this->insereDeputadoProposicao($ideCadastro,$idProposicao);
                }
            }
        } catch (Exception $e) {
            
        }
    }
}
?>