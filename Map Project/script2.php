<?php 

    // verificando a seleção de "Estou em"
    if (! isset ( $_POST ['myLocal'] )) {
        $meuLocal = 0;
    } else {
        $meuLocal = $_POST ['myLocal'];
    }

    $tipoLinha =  "1;5";

    if(isset ($_POST['tipoLinha'])){
        $tipoLinha =  $_POST['tipoLinha'];
    }

    $tipoLinhaArray = explode (";", $tipoLinha);

    // pegar circular (linha) e qual circulino
    $circular = $tipoLinhaArray [0];
    $circulino = $tipoLinhaArray [1];

    // simular a consulta para saber qual linha está selecionada

    $checked1 = "";
    $checked2 = "";
    $checked3 = "";

    if($tipoLinha == "1;5"){
        $checked1 = "checked";
    }else if($tipoLinha == "2;6"){
        $checked2 = "checked";
    } else if($tipoLinha == "5;0"){
        $checked3 = "checked";
    }

    $display1 = 'inline';
    $display2 = 'none';

    if($circular == 5){
        $display1 = 'none';
        $display2 = 'inline';
    }
    
    $scriptJS = "var busStops = new Array;\n";

    if($circular != 6){
        $nomeArquivo = "Rotas/pontos_rota$circular.txt";

        $arquivo = fopen($nomeArquivo, 'r');
    
        while(!feof($arquivo)){
    
            $linha = fgets($arquivo, 1024);
            $scriptJS .= "    busStops.push(createBusStop([" . substr($linha, 0, -1) . "]));\n";
        }
    
        fclose($arquivo);
    
    }


?>


<script>

    var idCircularLinha = <?php print $circular?>;
    var idCirculino = <?php print $circulino?>;

    function createBusStop(position){

        let icon = L.icon({
                        iconUrl: "./img/buildings.png",
                        iconSize: [20,32]
        });

        let options = {
                icon: icon,
                zIndexOffset: 900,
                draggable: false
            };

        let busStop = L.marker(position, options);
        
        return busStop;
    }

    <?php echo $scriptJS ?>

</script>