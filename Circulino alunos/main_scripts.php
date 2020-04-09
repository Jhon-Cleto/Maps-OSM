<script>
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

        
        //LENDO VELOCIDADE MEDIA NO BANCO POSTGRE PARA UMA DETERMINADA HORA, DIA E MES
        // if(!$_REQUEST['hora']) $_REQUEST['hora'] = 2;
        // if(!$_REQUEST['dia']) $_REQUEST['dia'] = 3;
        // if(!$_REQUEST['mes']) $_REQUEST['mes'] = 3;
        // $hora_atual = $_REQUEST['hora'];
        // $dia_atual = $_REQUEST['dia'];
        // $mes_atual = $_REQUEST['mes'];

        // $conexao = pg_connect("host=circulino-db.postgres.database.azure.com port=5432 dbname=circdb user=root_cc@circulino-db password=Smartcampus9 sslmode=require");
        // $query = "SELECT * from slice WHERE weekday=$dia_atual and month=$mes_atual and hour=$hora_atual";
        // $result = pg_query ($conexao, $query);
            
        // echo "var vel_media = ". pg_fetch_assoc($result)['speed'] . ";";
        echo "var vel_media = 8;";
        
        for($j = 0; $j < $i; $j ++) {
            $prox = ($j + 1) % $i;
            echo "tempos[$j] = getDistance(latitudes[$j], longitudes[$j], latitudes[$prox], longitudes[$prox]) * 1000/vel_media;";
        }
        
       
?>
    
    numpontos = latlng.length;
    //INICIALIZANDO O MAPA NA TELA ATRAVES DA CHAMADA A API DO LEAFLET
    $('#map').css("height", $(document).height());
            var distance = 14;        
            var map = L.map('map').setView([-22.817005, -47.069752], 15);
                    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoiZmVycGFyZWRlcyIsImEiOiJjazA3YmJrb28wam40M2lxYnZ1Nm5nNTBvIn0.4w3XZqshy2t6elQp3LUhfA', {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                    maxZoom: 18,
                    id: 'mapbox.streets',
                    accessToken: 'pk.eyJ1IjoiZmVycGFyZWRlcyIsImEiOiJjazA3YmJrb28wam40M2lxYnZ1Nm5nNTBvIn0.4w3XZqshy2t6elQp3LUhfA'
    }).addTo(map);

    //CRIANDO ICONES DOS PINS QUE SERAO EXIBIDOS NO MAPA 
     var BusStationIcon = L.icon({
        iconUrl: 'bus_station2.png',
        iconSize: [25, 70]
    });
    var BusIcon = L.icon({
        iconUrl: 'busicon.png',
        iconSize: [30, 30],
    });
    
//    var marker = L.marker(latlng[0], {icon: BusStationIcon}).addTo(map).on('click', alert('oi'));
    
    //FUNCAO QUE EH CHAMADA AO TOCAR EM UM PIN NO MAPA
    function ponto_clicado(e) {
        document.getElementById('texto_modal').innerHTML = "Tempo estimado para chegada: <br><b align=\"center\" style=\"font-size:30px;\">"+tempo_estimado([e.latlng.lat, e.latlng.lng])+" minutos</b>"; 
        document.getElementById('teste_modal').click();
    }
    
    function tempo_estimado(latlongi) {
        tempo_total = 0;
        
        //DESCOBRINDO O INDICE DO PONTO CLICADO
        var i = 0;
        while((latlng[i][0] != latlongi[0] || latlng[i][1] != latlongi[1]) && i < numpontos) {
            i ++;    
        }
        
        //ESTIMANDO O TEMPO
        var pos_atual = [onibus.getLatLng().lat, onibus.getLatLng().lng];
        tempo_total += (getDistance(latlng[wp_atual][0], latlng[wp_atual][1], pos_atual[0], pos_atual[1]) / vel_media);
        
        i = (wp_atual+1)%numpontos;
        console.log("i no comeco = "+i);
        while(latlng[i][0] != latlongi[0] || latlng[i][1] != latlongi[1] ){
            tempo_total += tempos[i];
            i = (i+1)%numpontos;
        }
        console.log("i no fim = "+i);
        
        //CONVERTENDO DE MILISEGUNDOS PARA MINUTOS
        return (tempo_total / (1000 * 60));
    }
    
    var counter = 0;
    var last_min = -1;
    var stations_wp = new Array();
    //FUNCAO QUE INSERE PIN NO MAPA
    
    function ColocaPin(latlongi) {  
        if(latlongi == latlng[0]) return;
        L.marker(latlongi, {icon: BusStationIcon}).addTo(map).on('click', ponto_clicado);//bindPopup("Tempo estimado para chegada: <br><b align=\"center\" style=\"font-size:30px;\">"+parseInt(tempo_estimado(find_wp(latlongi)))+" minutos</b>").openPopup();
    }
//    function callback(e) {
//       e.bindPopup("Tempo estimado para chegada: <br><b align=\"center\" style=\"font-size:30px;\">"+parseInt(tempo_estimado(find_wp([e.latlng.lat, e.latlng.lng])))+" minutos</b>").openPopup();
//    }

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

    

    //FUNCAO QUE SERA EXECUTADA AO TOCAR NA TROCA DE ROTAS
    function trocar_rota() {
        var rota = $('#rotas option:selected').val();
        var proximapagina = location.href.split('?')[0]+"?rota="+rota;
        location.href = proximapagina;         
    }
    
    //EXIBINDO O ITINERARIO NO MAPA
    itinerary = L.polyline(waypoints, {color: 'darkblue'});
    map.addLayer(itinerary);

    //SCRIPTS QUE CRIAM A SIMULACAO DE MOVIMENTO DOO ONIBUS
//    var local_onibus = 0;
//    var onibus = L.marker([latitudes[local_onibus], longitudes[local_onibus]], {icon: BusIcon}).addTo(map);
//    onibus
//
//    function atualiza_local() {
//        setTimeout(function() {
//            local_onibus = (local_onibus+1) % latitudes.length;
//            map.removeLayer(onibus);
//            onibus = L.marker([latitudes[local_onibus], longitudes[local_onibus]], {icon: BusIcon}).addTo(map);
////            onibus.movingMarker();
//            atualiza_local();
//        }, getDistance(latitudes[local_onibus-1], longitudes[local_onibus-1], latitudes[local_onibus], longitudes[local_onibus]) * 2 * 1000 / vel_media);
//    }
//    atualiza_local();

    /***********************TESTE**********************/
    wp_atual = 0;
    wp_prox = (wp_atual+1)%numpontos;
    var onibus = L.Marker.movingMarker([latlng[wp_atual], latlng[wp_prox]], tempos[wp_atual], {icon: BusIcon}).addTo(map);
    onibus.setZIndexOffset(100);
    onibus.start();
    movimenta_onibus();

    function movimenta_onibus() {
        setTimeout(function(){
            map.removeLayer(onibus);
            wp_atual = (wp_atual+1) % numpontos;
            wp_prox = (wp_atual+1)%numpontos;
            onibus = L.Marker.movingMarker([latlng[wp_atual], latlng[wp_prox]], tempos[wp_atual], {icon: BusIcon}).addTo(map);
            onibus.setZIndexOffset(100);
            onibus.start();
            movimenta_onibus();
        }, tempos[wp_atual]);
    }
    
//     var onibus = L.Marker.movingMarker(latlng, tempos, {icon: BusIcon}).addTo(map); 
//     onibus.start();
    /***********************TESTE**********************/


    <?php

        //INSERINDO PONTOS DE ONIBUS NO MAPA
        $nome_arquivo = "Rotas/pontos_rota$rota.txt";
        $arquivo = fopen ($nome_arquivo, 'r');
        
        while(!feof($arquivo))
        {   
            //LENDO LINHA A LINHA DO ARQUIVO
            $linha = fgets($arquivo, 1024);
            echo "ColocaPin([".substr($linha, 0, -1)."]);"; 
//            echo "L.marker([".substr($linha, 0, -1)."], {icon: BusStationIcon}).addTo(map).bindPopup(\"Voc&ecirc; est&aacute; aqui!\").openPopup();";
        }
    ?>    
    
//    function ultimo_wp_percorrido(latlongi) {
//        min = 100000000;
//        min_index = -1;
//        var dist;
//        var i;
//        for(i = 0; i < latlng.length; i++){
//            dist = getDistance(latlongi[0], latlongi[1], latlng[i][0], latlng[i][1]);
//            if(dist < min && i > last_min) {
//                min = dist;
//                min_index = i+1;
//            }
//        }
//        
//        vizinho = (min_index+1)%latlng.length;
//        vizinho_onibus = getDistance(latlongi[0], latlongi[1], latlng[vizinho][0], latlng[vizinho][1]);
//        vizinho_min = getDistance(latlng[min_index][0], latlng[min_index][1], latlng[vizinho][0], latlng[vizinho][1]);
//        
//        var res;
//        if(vizinho_onibus < vizinho_min) res = vizinho;
//        else res = min_index;
//        return res;
//    }
    
    
    /***************************TESTE*************************/

    
    function find_wp(latlongi) {
        counter ++;
        min = 100000000;
        min_index = -1;
        var dist;
        var i;
        for(i = 0; i < latlng.length; i++){
            dist = getDistance(latlongi[0], latlongi[1], latlng[i][0], latlng[i][1]);
            if(dist < min && i > last_min) {
                min = dist;
                min_index = i+1;
            }
        }
        
//        last_min = min_index;
//        stations_wp[stations_wp.length] = last_min;
//        if(stations_wp.length >= 27) {
//            console.log(stations_wp);
//        }
//        
//        console.log(onibus.getLatLng());
//        console.log("Ponto "+counter+" ("+latlongi[1]+","+latlongi[0]+",0),  min = "+min+", min_index = "+min_index);
        return counter.toString();
    }
   
    /***************************TESTE*************************/
    
    
</script>