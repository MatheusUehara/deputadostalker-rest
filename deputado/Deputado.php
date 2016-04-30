<?php

class Deputado{

    private $conn;

    function __construct() {
        require_once '../include/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /*
    * Obtem os detalhes do deputado a partir do Web Service da camara e insere no Banco de Dados
    */
    function inserirDetalhesDeputado($ideCadastro){

        require_once '../comissao/Comissao.php';
        require_once '../gabinete/Gabinete.php';
        require_once '../partido/Partido.php';

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


        $comissao = new Comissao();
        $comissao->insereComissao($obj);

        $partido = new Partido();
        $partido->inserePartidos($obj);

        $gabinete = new Gabinete();
        $gabinete->insereGabinete($obj);


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

    /*
    * Pega os deputados o WebService da camara e insere no banco do nosso Servidor
    */
    function obterDeputadosCamara(){
        
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

    /*
    * Retorna todos os deputados
    */
    function getDeputados(){
        $stmt = $this->conn->prepare("SELECT * FROM deputado");
        $stmt->execute();
        $deputados = $stmt->get_result();
        $stmt->close();
        return $deputados;
    }
}
?>