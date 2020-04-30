<?php 

    $tipoLinha = "1;5";

    if(isset ($_POST['tipoLinha'])){
        $tipoLinha =  $_POST['tipoLinha'];
    }

    $tipoLinhaArray = explode (";", $tipoLinha);



    // pegar circular (linha) e qual circulino
    $circular = $tipoLinhaArray [0];
    $circulino = $tipoLinhaArray [1];

?>


<script>

    var idCircularLinha = <?php print $circular?>;
    var idCirculino = <?php print $circulino?>;


</script>