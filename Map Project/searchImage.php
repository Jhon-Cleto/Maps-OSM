<?php

$fileName = $_POST['fileName'];

if(file_exists("img/fotosPontosCI/". $fileName .".JPG")){
    echo "img/fotosPontosCI/". $fileName .".JPG";
} 
else{
    echo "img/fotosPontosCI/semImagem.png";
}

?>
