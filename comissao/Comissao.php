<?php

/**
 * Created by PhpStorm.
 * User: Matheus Uehara
 * Date: 29/04/2016
 * Time: 22:53
 */
class Comissao{

    private $conn;

    function __construct() {
        require_once '../include/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
    
    /*
     * Retorna os relacionamentos de todos os deputados e suas comissoes
     */
    function getComissoesDeputados($ideCadastro){
        $stmt = $this->conn->prepare("SELECT * FROM deputado_has_orgao where ");
        $stmt->execute();
        $deputados = $stmt->get_result();
        $stmt->close();
        return $deputados;
    }


    /*
    * Retorna a lista de comissoes de um deputado especifico
    */
    function getComissaoDeputado($ideCadastro){

    }


    /*
    * Cria o relacionamento entre o deputado e a comissão no banco de dados
    */
    function insereDeputadoComissao($ideCadastro, $idOrgao){
        $stmt = $this->conn->prepare("INSERT INTO deputado_has_orgao (deputado_ideCadastro,orgao_idOrgao) values (? , ? )");
        $stmt->bind_param( "ii", $ideCadastro,$idOrgao);
        $stmt->execute();
        $stmt->close();
    }


    /*
     * Retorna todas as comissoes
     */
    function getComissoes(){
        $stmt = $this->conn->prepare("SELECT * FROM orgao");
        $stmt->execute();
        $comissoes = $stmt->get_result();
        $stmt->close();
        return $comissoes;
    }


    /*
    * Adiciona a comissão no banco de dados
    */
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
}