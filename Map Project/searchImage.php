<?php

$fileName = $_POST['fileName'];

if(file_exists("img/fotosPontosCI/". $fileName .".jpg")){
    echo "img/fotosPontosCI/". $fileName .".jpg";
} 
else{
    echo "img/fotosPontosCI/semImagem.png";
}

?>