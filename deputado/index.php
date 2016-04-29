<!DOCTYPE HTML>
<html lang='pt-BR'>
    <head>
        <title>Deputado Route</title>
    </head>
    <body>
        <?php
            header('Content-Type: text/html; charset=utf-8');
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
                echo "Base preenchida com sucesso!";
            });

            $app->run();
        ?>
    </body>
</html>
