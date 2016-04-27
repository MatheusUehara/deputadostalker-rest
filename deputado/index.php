<?php
require_once '../libs/vendor/autoload.php';
 
$app = new \Slim\Slim();
$app->get('/:name', function ($name) {
    echo "Hello, $name"."<br>";
});
$app->run();

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'http://www.camara.gov.br/SitCamaraWS/Deputados.asmx/ObterDeputados',
    CURLOPT_USERAGENT => 'Codular Sample cURL Request'
));
$resp = curl_exec($curl);
curl_close($curl);

$xml_object = simplexml_load_string($resp);

$json = json_encode(simplexml_load_string($resp));

$obj = json_decode($json);

foreach($obj -> deputado as $item){
    echo "<img src= '" . $item -> urlFoto ."'/>"."<br />";
    echo "<strong>Título:</strong> ".utf8_decode($item -> ideCadastro)."<br />";
    echo "<strong>Link:</strong> ".utf8_decode($item -> nome)."<br />";
    echo "<strong>Descrição:</strong> ".utf8_decode($item -> nomeParlamentar)."<br />";
    echo "<strong>Autor:</strong> ".utf8_decode($item -> sexo)."<br />";
    echo "<strong>Data:</strong> ".utf8_decode($item -> partido)."<br />";
    echo "<br />";
}

?>

