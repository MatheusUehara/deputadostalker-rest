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

    function getIdeDeputado( $matricula ){
        $stmt = $this->conn->prepare("SELECT ideCadastro FROM deputado where matricula = ?");
        $stmt ->bind_param('i', $matricula);
        $stmt->execute();
        $result = $stmt->get_result();
        $id = $result->fetch_assoc();
        
        $stmt->close();
        return $id['ideCadastro'];


    }

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


    function obterPresencaDeputado($dataIni,$dataFim,$matricula){

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.camara.gov.br/SitCamaraWS/sessoesreunioes.asmx/ListarPresencasParlamentar?dataIni='.$dataIni.'&dataFim='.$dataFim.'&numMatriculaParlamentar='.$matricula.'',
            CURLOPT_USERAGENT => 'Deputado Stalker getRequest'
        ));

        $resp = curl_exec($curl);
        curl_close($curl);
        try {
            $json = json_encode(simplexml_load_string($resp));
            
        } catch (Exception $e) {
            return "error"; 
        }

        $obj = json_decode($json);
        $return = array();
        
        $nomeParlamentar = $obj->nomeParlamentar;

        $ideCadastro = $this->getIdeDeputado($matricula);

        if ( is_array($obj->diasDeSessoes2->dia)) {
            // INteração para percorrer todos os itens retornado da pesquisa ao webservice da camara.
            $return = $this->interarListaFrequencia($obj,$return);
        }else{
            
            $return = $this->addFrequencia($obj,$return);        
        }

        return $return;   
    }
    
        function interarListaFrequencia($obj,$return){
        foreach ($obj ->diasDeSessoes2-> dia as $dia) {
            
            $date = $dia->data;
            $tmp = explode('/', $date);
            $lista['data'] = $tmp[2].'/'.$tmp[1].'/'.$tmp[0];
            $lista['frequencia'] = $dia -> frequencianoDia;
            $lista['justificativa']= $dia -> justificativa;
            $lista['qtdeSessoes']= $dia -> qtdeSessoes;
            if (!is_string($lista['justificativa'])) {
                $lista['justificativa'] = NULL;
            }

            //codigo de inserção no das relações de presença no BD
            $stmt = $this->conn->prepare("INSERT INTO data(idData) 
                values ( ? )");
            $stmt->bind_param( "s", $lista['data'] );
            $stmt->execute();
            //$stmt->close();

            //Adciona o relacionamento entre a tabela data e a tabela deputado

            $stmt = $this->conn->prepare("INSERT INTO data_has_deputado( data_idData, deputado_ideCadastro, frequencia, justificativa, qtdeSessoes) 
                values (? , ? , ? , ? , ? )");
            $stmt->bind_param( "sissi", $lista['data'], $ideCadastro, $lista['frequencia'], $lista['justificativa'], $lista['qtdeSessoes']);
            $stmt->execute();
            $stmt->close();
            array_push($return, $lista);

        }
        return $return;

    }


    function addFrequencia($obj,$return){
        
        $dia = $obj ->diasDeSessoes2-> dia;
        $date = $dia->data;
        $tmp = explode('/', $date);
        $lista['data'] = $tmp[2].'/'.$tmp[1].'/'.$tmp[0];
        $lista['frequencia'] = $dia -> frequencianoDia;
        $lista['justificativa']= $dia -> justificativa;
        $lista['qtdeSessoes']= $dia -> qtdeSessoes;
        if (!is_string($lista['justificativa'])) {
            $lista['justificativa'] = NULL;
        }

        //codigo de inserção no das relações de presença no BD
        $stmt = $this->conn->prepare("INSERT INTO data(idData) 
            values ( ? )");
        $stmt->bind_param( "s", $lista['data'] );
        $stmt->execute();
        //$stmt->close();

        //Adciona o relacionamento entre a tabela data e a tabela deputado

        $stmt = $this->conn->prepare("INSERT INTO data_has_deputado( data_idData, deputado_ideCadastro, frequencia, justificativa, qtdeSessoes) 
            values (? , ? , ? , ? , ? )");
        $stmt->bind_param( "sissi", $lista['data'], $ideCadastro, $lista['frequencia'], $lista['justificativa'], $lista['qtdeSessoes']);
        $stmt->execute();
        $stmt->close();
        array_push($return, $lista);

        return $return;

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