<?php

/**
 * Created by PhpStorm.
 * User: Matheus Uehara
 * Date: 29/04/2016
 * Time: 23:17
 */
class Partido{

    private $conn;

    function __construct() {
        require_once '../include/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /*
    * Retorna todos os gabinetes
    */
    function getPartidos(){
        $stmt = $this->conn->prepare("SELECT * FROM partido");
        $stmt->execute();
        $partidos = $stmt->get_result();
        $stmt->close();
        return $partidos;
    }

    /*
    * Insere um partido no Banco de dados
    */
    function inserePartidos($obj){
        $idPartido = $obj->Deputado->partidoAtual->idPartido;
        $nome = $obj->Deputado->partidoAtual->nome;
        $stmt = $this->conn->prepare("INSERT INTO partido (idPartido, nome) values (? , ?)");
        $stmt->bind_param( "ss", $idPartido , $nome);
        $stmt->execute();
        $stmt->close();
    }
}