<?php
require_once '../libs/vendor/autoload.php';
 
$app = new \Slim\Slim();
$app->get('/:name', function ($name) {
    echo "Hello , $name"."<br>";
});

$app->get('/atualizarBaseDeputados/', function () {
    require_once 'Deputado.php';
    $obterdeputado = new Deputado();
    // execução de obtençao de detalhes de um deputado especifico
    //$obterdeputado->inserirDetalhesDeputado(160617,55);
    $obterdeputado->obterDeputados();
});
$app->run();
?> 