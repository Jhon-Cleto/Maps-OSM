 <?php 
////DESCOMENTE AQUI PARA FORCAR O USO DO CACHE DO CLIENTE
//if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
//   header('HTTP/1.1 304 Not Modified');
//   die();
//}

//function caching_headers ($file, $timestamp) {
//    $lastModified=filemtime($_SERVER['SCRIPT_FILENAME']);
//    $gmt_mtime = gmdate("D, d M Y H:i:s T", $lastModified);
//    header('ETag: "'.md5($timestamp.$file).'"');
//    header('Last-Modified: '.$gmt_mtime);
//    header('Cache-Control: public, proxy-revalidate, max-age=3600');
//}
//
//caching_headers ($_SERVER['SCRIPT_FILENAME'], filemtime($_SERVER['SCRIPT_FILENAME']));

?>
<!DOCTYPE html>
<html>
<head>
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css"
       integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
       crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"
       integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og=="
       crossorigin=""></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    
    <!-- INICIALIZANDO O JQUERY -->
    <script type="text/javascript" src="jquery-3.4.1.js"></script>

    <!-- INICIALIZANDO O BOOTSTRAP -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script  type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script  type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="package/MovingMarker.js"></script>
</head>
<body style="margin: 0; padding: 0">
    <div align="center" style="position: absolute; width: 100%; margin-top: 1%; z-index: 9999; height: 30%" >
            <div align="center" style="border-radius: 6px; display: none; border-style: solid; border-color: black; border-width: 2px; background:  white; width: 30%; padding: 5">
                    <strong>MC855<br>
                    PROJETO CIRCULINO</strong>
            </div>
            <div align="center" style="border-radius: 6px; border-style: solid; border-color: black; border-width: 2px; background:  white; width: 320px;">
                Escolher Circular:<br>
                <select id="rotas" onchange="trocar_rota();">
                    <option value="1" <?php if ($_REQUEST['rota'] < 2) echo selected;?>>Circular 1 (sentido anti-hor&aacute;rio)</option>
                    <option value="2" <?php if ($_REQUEST['rota'] == 2) echo selected;?>>Circular 2 - via FEC (sentido hor&aacute;rio)</option>
                    <option value="3" <?php if ($_REQUEST['rota'] == 3) echo selected;?>>Circular 2 - via Museu (sentido hor&aacute;rio)</option>
                    <option value="4" <?php if ($_REQUEST['rota'] == 4) echo selected;?>>Circular Noturno (sentido hor&aacute;rio)</option>
                </select>
            </div>
        <Br>
        <input type="button" value="teste_modal" id="teste_modal" data-toggle="modal" data-target="#alert" style="display: none" >
            
    </div>
    </table>

    <div style="width: 100%; height: 100%">
        <div id="mapid"></div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>
    <script>
        
        $('#mapid').css("height", $(document).height());
        var distance = 14;        
        var mymap = L.map('mapid').setView([-22.817005, -47.069752], 15);
                L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoiZmVycGFyZWRlcyIsImEiOiJjazA3YmJrb28wam40M2lxYnZ1Nm5nNTBvIn0.4w3XZqshy2t6elQp3LUhfA', {
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                maxZoom: 18,
                id: 'mapbox.streets',
                accessToken: 'pk.eyJ1IjoiZmVycGFyZWRlcyIsImEiOiJjazA3YmJrb28wam40M2lxYnZ1Nm5nNTBvIn0.4w3XZqshy2t6elQp3LUhfA'
        }).addTo(mymap);
        
        var BusStationIcon = L.icon({
            iconUrl: 'bus_station2.png',
            iconSize: [25, 70]
        });
           
        var BusIcon = L.icon({
            iconUrl: 'busicon.png',
            iconSize: [25, 40],
        });
        
        waypoints = [];
        
        
        //FUNCOES AUXILIARES PARA CALCULO DA DISTANCIA ENTRE DOIS PONTOS REAIS NO MAPA, A PARTIR DE SUAS LAT E LONG
    function deg2rad(degrees) {
        return degrees * (Math.PI/180);
    }
    function getDistance(latitudeFrom, longitudeFrom, latitudeTo, longitudeTo) {

        var earthRadius = 6371000;

        var latFrom = deg2rad(latitudeFrom);
        var lonFrom = deg2rad(longitudeFrom);
        var latTo = deg2rad(latitudeTo);
        var lonTo = deg2rad(longitudeTo);

        var latDelta = latTo - latFrom;
        var lonDelta = lonTo - lonFrom;

        var angle = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin(latDelta / 2), 2) +
                    Math.cos(latFrom) * Math.cos(latTo) * Math.pow(Math.sin(lonDelta / 2), 2)));

        return (angle * earthRadius);
    }
<?php

        //    ini_set('display_errors', 1);
        //    ini_set('display_startup_errors', 1);
        //    error_reporting(E_ALL);

        //LENDO LATITUDES E LONGITUDES DO ARQUIVO DE ITINERARIOS E ATRIBUINDO A UM ARRAY EM JS
        if( $_REQUEST['rota'] ) $rota = $_REQUEST['rota'];
        else $rota = 1; 
        
        $nome_arquivo = "Rotas/itinerario_rota$rota.txt";
        $arquivo = fopen ($nome_arquivo, 'r');
        
        echo "waypoints = [];";
        $waypoints = "";
        
        echo "var latitudes = new Array();";
        echo "var longitudes = new Array();";
         echo "var latlng = new Array();";
         echo "var tempos = new Array();";
            
        $i = 0;
        while(!feof($arquivo))
        {
            //LENDO LINHA A LINHA DO ARQUIVO
            $linha = fgets($arquivo, 1024);
            //NO ARQUIVO (CRIADO A PARTIR DE UM KML DO GOOGLE MAPS), LONGITUDE VEM ANTES DE LATITUDE
            $coordenadas = explode(',', $linha); 
            $lat = $coordenadas[1];
            $lng = $coordenadas[0];
            
            echo "latitudes[$i] = $lat;";
            echo "longitudes[$i] = $lng;";
            
            echo "latlng[$i] = [$lat, $lng];";
            
            $waypoints .= " L.latLng($lat, $lng),";
            
            $i ++;
        }
            
        $waypoints = substr($waypoints, 0, -1);
        echo "waypoints = [$waypoints];";
            
        fclose($arquivo);
            
        echo "var vel_media = 8;";
        
        for($j = 0; $j < $i; $j ++) {
            $prox = ($j + 1) % $i;
            echo "tempos[$j] = getDistance(latitudes[$j], longitudes[$j], latitudes[$prox], longitudes[$prox]) * 1000/vel_media;";
        }
?>
        L.marker(waypoints.latLng, {icon: BusIcon});

        
        function trocar_rota() {
            var rota = $('#rotas option:selected').val();
            var proximapagina = location.href.split('?')[0]+"?rota="+rota+"&teste=123";
            location.href = proximapagina;
            
            //controlWalk.setWaypoints(rota2);            
        }

        itinerary = L.polyline(waypoints, {color: 'darkblue'});
        mymap.addLayer(itinerary);
        
        alert()
        
        var num = 0;
        function ColocaPin(latlongi) {        
            L.marker(latlongi, {icon: BusStationIcon}).addTo(mymap).on('click', function(e) {
                alert(e.latlng.lat);
                alert(e.latlng.lng);
            });
        }
        var onibus = L.Marker.movingMarker(latlng, tempos, {icon: BusIcon}).addTo(mymap);
        onibus.start();
<?php
        //INSERINDO PONTOS DE ONIBUS NO MAPA
        $nome_arquivo = "Rotas/pontos_rota$rota.txt";
        $arquivo = fopen ($nome_arquivo, 'r');
        
        while(!feof($arquivo))
        {   
            //LENDO LINHA A LINHA DO ARQUIVO
            $linha = fgets($arquivo, 1024);
            echo "ColocaPin([".substr($linha, 0, -1)."]);"; 
        }
    ?> 
    </script>
</body>
</html>	