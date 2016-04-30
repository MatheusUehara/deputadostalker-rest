<?php

/**
 * Created by PhpStorm.
 * User: Matheus Uehara
 * Date: 29/04/2016
 * Time: 23:00
 */
class Gabinete{

    private $conn;

    function __construct() {
        require_once '../include/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /*
     * Retorna o gabinete de um deputado especifico
     */
    function getGabinete($ideCadastro){

    }

    /*
     * Retorna todos os gabinetes
     */
    function getGabinetes(){
        $stmt = $this->conn->prepare("SELECT * FROM gabinete");
        $stmt->execute();
        $gabinetes = $stmt->get_result();
        $stmt->close();
        return $gabinetes;
    }

    /*
    * Insere um gabinete no Banco de Dados
    */
    function insereGabinete($obj){
        $idGabinete = $obj->Deputado->gabinete->numero;
        $anexo = $obj->Deputado->gabinete->anexo;
        $telefone = $obj->Deputado->gabinete->telefone;
        $stmt = $this->conn->prepare("INSERT INTO gabinete (idGabinete, anexo , telefone) values (? , ? , ?)");
        $stmt->bind_param( "iis", $idGabinete ,$anexo , $telefone);
        $stmt->execute();
        $stmt->close();
    }

}