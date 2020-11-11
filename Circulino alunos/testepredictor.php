<?php
//***************** SNIPPET PARA DEPURACAO DO CODIGO ********************
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
//**********************************************************************

function getDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
    //CONVERTENDO GRAUS PARA RADIANOS
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

    return ($angle * $earthRadius);
}

function distancia($lat1, $lon1, $lat2, $lon2) {

    $lat1 = deg2rad($lat1);
    $lat2 = deg2rad($lat2);
    $lon1 = deg2rad($lon1);
    $lon2 = deg2rad($lon2);
    
    $dist = (6371 * acos( cos( $lat1 ) * cos( $lat2 ) * cos( $lon2 - $lon1 ) + sin( $lat1 ) * sin($lat2) ) );
    $dist = number_format($dist, 2, '.', '');
    return $dist;
}

include("Predictor.php");

//CONSTRUINDO NOVO ARQUIVO DE ROTAS --> COORDENADA Z SUBSTITUIDA POR DISTANCIA ENTRE WAYPOINT ATUAL E O PROXIMO
$filename = "Rotas/itinerario_rota1.txt";
$file = fopen($filename, 'r');

$i = 0;

while(!feof($file))
{
    //LENDO LINHA A LINHA DO ARQUIVO
    $linha = fgets($file, 1024);
    $coordenadas = explode(',', $linha); 
    $lat[$i] = $coordenadas[1];
    $lng[$i] = $coordenadas[0];

    $i++;
}
fclose($file);

$i = 0;

$filename = "novo_itinerario_rota1.txt";
$file = fopen($filename, 'w+');

$next = ($i+1) % count($lat);

for($i = 0; $i < count($lat); $i++) {
    $linha = $lat[$i].", ".$lng[$i].", ".getDistance($lat[$i], $lng[$i], $lat[$next], $lng[$next]);
    echo $linha."<br>";
}

//$teste = new Predictor();
//$teste->SetCurrentLocation(array(-22.82685, -47.06145));
//
//$teste->SetRoute(1);
//
//$teste->readStops();
    

