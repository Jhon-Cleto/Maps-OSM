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

initialize();

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
    var options = {
        center: [-22.821677, -47.065283],
        zoom: 15
    }

    map = L.map('mymap', options);

    var CartoDB_Voyager = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
    });

    CartoDB_Voyager.addTo(map);

    insertKML();
    putBusMarker();
    putIAmHereMarker();    

};

//Função para colocar o marcador do onibus
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
        markerIAmHere.addTo(map);


    }
}
