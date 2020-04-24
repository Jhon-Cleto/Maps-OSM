
var traceRoute = false;
var map;

var markerBus;
var markerIAmHere;
var arrMarkers = [];
var arrInfoWindows = [];
var arrWaypts = [];
var arrPontoProxHorario = [];
var arrPontosOnibus = [];

var coordinates;
var marker;

var currentLatOnibus = -22.827216;
var currentLgnOnibus = -47.061095;

var currentLatUsuario = -22.817113;
var currentLngUsuario = -47.069672;

var currentVelocOnibus;
var statusCoordinates = 1;
var lastSend = "";
var lastAddress = "";

var countCoordsIsNull = 0;



var posicaoPontoInicial = L.latLng(-22.828016, -47.060825);
var posicaoCentroUnicamp = L.latLng(-22.821677, -47.065283);


initialize();

map.addEventListener('zoom', setVisibleMarkers);

setInterval(buscarPosicaoOnibusAjax, 3000);

function insertKML(){
    fetch('1.kml')
    .then(res => res.text())
    .then(kmltext => {
        // Create new kml overlay
        const parser = new DOMParser();
        const kml = parser.parseFromString(kmltext, 'text/xml');
        const track = new L.KML(kml);
        map.addLayer(track);

        // Adjust map to show the kml
        const bounds = track.getBounds();
        map.fitBounds(bounds);
    });    
}


function initialize(){
    let options = {
        center: posicaoCentroUnicamp,
        zoom: 15
    }

    map = L.map('mymap', options);

    let CartoDB_Voyager = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
    });

    CartoDB_Voyager.addTo(map);

    insertKML();
    putBusMarker();
    putIAmHereMarker();
    buscarPosicaoOnibusAjax();    

};

//Função para colocar o marcador do ônibus
function putBusMarker() {
    if(markerBus != null){
        markerBus.removeFrom(map);
    }

    if(map != null){
        let busPosition = L.latLng(currentLatOnibus, currentLgnOnibus);
        
        let options = {
            zIndexOffset: 1000,
            draggable: false
        };
        
        markerBus = L.marker(busPosition, options);

        if(statusCoordinates == 1){
            let busIcon = L.icon({
                iconUrl: "./img/bus.png",
                iconSize: [24, 32]
            });

            markerBus.setIcon(busIcon);
        }

        else if(statusCoordinates == 2){
            let busIcon = L.icon({
                iconUrl: "./img/busEstimatePos.png",
                iconSize: [24, 32]
            }); 

            markerBus.setIcon(busIcon);           
        }

        else if(statusCoordinates == 3){
            let busIcon = L.icon({
                iconUrl: "./img/busNoConnection.png",
                iconSize: [24, 32]
            }); 

            markerBus.setIcon(busIcon);                
        }

        markerBus.addTo(map);
    }
}

// Função para colocar o marcador do usuário
function putIAmHereMarker(){

    //remover o marcador do mapa
    if(markerIAmHere != null) {
        markerIAmHere.removeFrom(map);
    }

    if(map != null){
        let userPosition = L.latLng(currentLatUsuario, currentLngUsuario);
        
        let options = {
            title: 'Arraste o marcador para indicar onde você está',
            icon: L.icon({iconUrl: './img/iamhere.png', iconSize: [50,50]}),
            zIndexOffset: 1000,
            draggable: true
        }; 
        
        markerIAmHere = L.marker(userPosition, options);

        let popUp = L.popup();
        popUp.setContent('Voc&#234; est&#225; aqui.');
        markerIAmHere.bindPopup(popUp);

        markerIAmHere.addEventListener('click', function(){
                                        markerIAmHere.openPopup();
                                        });
        
        markerIAmHere.addEventListener('dragend', function(){
                                        currentLatUsuario = markerIAmHere.getLatLng().lat;
                                        currentLngUsuario = markerIAmHere.getLatLng().lng;
                                        });  
        
        markerIAmHere.addTo(map);


    }
}

// Função para buscar e atualizar a posição do ônibus
function buscarPosicaoOnibusAjax(){

    var idCircularLinha = 1;
    var idCirculino = 5;

    $.ajax({
        url : 'https://www.prefeitura.unicamp.br/posicao/site/linha/'+idCircularLinha+'/circulino/'+idCirculino,
        type : 'GET',
        dataType: 'json',
        success: function(data){
            
            currentLatOnibus = data.latitude;
            currentLgnOnibus = data.longitude;
            currentVelocOnibus = data.velocidadeMedia;
            statusCoordinates = data.status; 
            lastAddress = data.endereco;
            lastSend = data.ultimoEnvio; 

            if (currentLatOnibus == null || currentLgnOnibus == null){
                countCoordsIsNull++;
            } else {
                countCoordsIsNull = 0;
                 putBusMarker();
            }
        } 
    });
}

function onibusEstaPontoInicial(){
    let busPos = L.latLng(currentLatOnibus, currentLgnOnibus);
    return (distanceAtoB(busPos, posicaoPontoInicial) <= 120);
}

function usuarioEstaPontoInicial(){
    let userPos = L.latLng(currentLatUsuario, currentLngUsuario);
    return(distanceAtoB(userPos, posicaoPontoInicial) <= 120);
}

// Função que retorna a distância em metros entre dois pontos
function distanceAtoB(pointA, pointB){
			
    latOrigem = pointA.lat(); 
    lngOrigem = pointA.lng();

    latDestino = pointB.lat();
    lngDestino = pointB.lng();

    
    distancia = 6371000*Math.acos(Math.cos(Math.PI*(90-latDestino)/180)*Math.cos((90-latOrigem)*Math.PI/180)+Math.sin((90-latDestino)*Math.PI/180)*Math.sin((90-latOrigem)*Math.PI/180)*Math.cos((lngOrigem-lngDestino)*Math.PI/180));

    return distancia;
}

function setVisibleMarkers(){

    var value;
    
    if (map.getZoom() < 15) {
        value = 0;
    } else {
        value = 1;
    }
    
    for (i=0; i<arrMarkers.length; i++) {
        arrMarkers[i].setVisible(value);
    }

    markerIAmHere.setOpacity(value);
}

// Corrigir arredondamento quando o último dígito for cinco
function fixRounding(value) {

    var strDistance = value.toString();
    var digit = strDistance.charAt(strDistance.length-1);
    
    if (digit == '5'){
        return 0.001;
    } else if (digit == '4') {
        return 0.002;
    }

    return 0;
}