
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

//var idCircularLinha = 1;

const LINHA_MORADIA = 5;
const LINHA_NOTURNO = 3;

var posicaoPontoInicial = L.latLng(-22.828016, -47.060825);
var posicaoCentroUnicamp = L.latLng(-22.821677, -47.065283);

setInterval(function(){
            buscarPosicaoOnibusAjax();
            if(statusCoordinates != 3){
                showWhereIsBus();
            }
        }, 3000);


// enviando o form
function submitServico(){
    frm = window.document.form
    frm.action = 'map.php'
    frm.submit()
}

function getPosFromGPS(){
    var options = {enableHighAccuracy: true, maximumAge: 0};
    navigator.geolocation.getCurrentPosition(setLatLng, error, options);
}

//função para definir latitude e longitude atual do usuário
function setLatLng(position) {
    var crd = position.coords;
    
    currentLatUsuario = position.coords.latitude;
    currentLngUsuario = position.coords.longitude; 

    putIAmHereMarker();
    

}

// função para retornar erro da geolocalizacao
function error(err) {
        console.warn('ERROR(' + err.code + '): ' + err.message);
}


function insertKML(){
    fetch("https://www.prefeitura.unicamp.br/apps/site/kml/circular/"+idCircularLinha+".kml?rev=1")
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
    putBusMarker(idCircularLinha);
    putIAmHereMarker();
    buscarPosicaoOnibusAjax();   
    map.addEventListener('zoom', setVisibleMarkers); 

}

function setLocation(){

    var obj = document.getElementsByName("myLocal");

    if (obj[0].checked){	
        currentLatUsuario = -22.817113;
        currentLngUsuario = -47.069672;
    
    } else if (obj[1].checked){ 
        
        if (navigator.geolocation) {
            getPosFromGPS();
        } else {
            document.getElementById("container").innerHTML = '<p align="center">Sinto muito, mas o servi&#231;o de geolocaliza&#231;&#227;o n&#227;o &#233; suportado por seu navegador.</p>';
        }
    } 

    if(map == null){
        initialize();
    }
        
}

//Função para colocar o marcador do ônibus
function putBusMarker(linha) {
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

        var centralizarNoOnibus = document.getElementById("chkCentralizarNoOnibus");
			
        // centralizar o mapa
        if (centralizarNoOnibus.checked){		
            if (!isNaN(markerBus.getLatLng().lat && !isNaN(markerBus.getLatLng().lng))){
                if (linha != LINHA_MORADIA) {map.setView(markerBus.getLatLng());}
                else {}
            }
        }
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

    if (idCircularLinha != LINHA_MORADIA){
        //Linhas internas
        //var idCirculino = 5;

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
                     putBusMarker(idCircularLinha);
                }
            } 
        });
    }

    else {
        //linha moradia
        $.ajax({
            url :'https://www.prefeitura.unicamp.br/posicoes/site/linha/'+idCircularLinha,
            type : 'GET',
            dataType: 'json',
            success: function(data){

                 currentLatOnibus = [];
                 currentLngOnibus = [];
                 statusCoordinates = [];

                for (var obj in data){
                    for (i=0; i<data[obj].length; i++){
                        currentLatOnibus.push(data[obj][i]['latitude']);
                        currentLngOnibus.push(data[obj][i]['longitude']);
                        statusCoordinates.push(data[obj][i]['status']);
                        currentVelocOnibus.push(data[obj][i]['velocidadeMedia']);
                        lastAddressArray.push(data[obj][i]['endereco']); 
                    }
                }   

                putBusMarker(idCircularLinha);
                
            } 
        });
    }

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
			
    latOrigem = pointA.lat; 
    lngOrigem = pointA.lng;

    latDestino = pointB.lat;
    lngDestino = pointB.lng;

    
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
        arrMarkers[i].setOpacity(value);
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

function showWhereIsBus(){

    var msg = "";

    msg ='<span style="font-weight: bold">Atualmente o &#244;nibus est&#225; em ' + lastAddress;
    
    if (onibusEstaPontoInicial() && lastAddress.indexOf("Sabin") != -1){
        msg += " (ponto inicial).";
    }

    msg += '</span>';
    msg += '<br/><span style="font-weight: bold">A velocidade média atual é ' + currentVelocOnibus.toString() + ' km/h.</span>';

    document.getElementById("endereco").innerHTML = msg;  

}


 // Traçar a rota e exibir distância e tempo
function route(){

    traceRoute = true;

    currentLatOnibus = markerBus.getLatLng().lat;
    cuurentLngOnibus = markerBus.getLatLng().lng;

    calcularDistanciaTempo = true;
    msgDistanciaTempo = "<span style=\"font-weight: bold\">";

    idxPontoMaisProximoUsuario = 0;
    usuarioPosicao = L.latLng(markerIAmHere.getLatLng());
    distanciaUsuarioAPonto = 9999999999;

    for (i=0; i<arrPontosOnibus.length; i++){
        if (arrPontosOnibus[i].unidade != 'FICTICIO'){
            busStop = L.latLng(arrPontosOnibus[i].lat, arrPontosOnibus[i].lng);
            distanceTmp = distanceAtoB(busStop, usuarioPosicao);

            if (distanceTmp < distanciaUsuarioAPonto) {
                idxPontoMaisProximoUsuario = i;
                distanciaUsuarioAPonto = distanceTmp;
            }
        }
    }		
    
    // setando o ponto de ônibus mais próximo ao usuário
    pontoMaisProximoUsuario = L.latLng(arrPontosOnibus[idxPontoMaisProximoUsuario].lat, arrPontosOnibus[idxPontoMaisProximoUsuario].lng);

    if (!onibusEstaPontoInicial() && !usuarioEstaPontoInicial()){
        
        //verificando o ponto mais próximo do ônibus e a sua distãncia do ônibus ao ponto.
        idxPontoMaisProximoOnibus=0;
        onibusPosicao = L.latLng(markerBus.getLatLng()); 
        distanciaOnibusAPonto = 9999999999;

        for (i=0; i<arrPontosOnibus.length; i++){
            if (arrPontosOnibus[i].unidade != 'FICTICIO'){
                busStop = L.latLng(arrPontosOnibus[i].lat, arrPontosOnibus[i].lng);
                distanceTmp = distanceAtoB(busStop, onibusPosicao);

                if (distanceTmp < distanciaOnibusAPonto) {
                    if (i > idxPontoMaisProximoOnibus ) { // para evitar pegar pontos fora da sequência
                        idxPontoMaisProximoOnibus = i;
                        distanciaOnibusAPonto = distanceTmp;
                    }
                }
            }
        }
        
        // setando o ponto de ônibus mais próximo do ônibus
        pontoMaisProximoOnibus = L.latLng(arrPontosOnibus[idxPontoMaisProximoOnibus].lat, arrPontosOnibus[idxPontoMaisProximoOnibus].lng);

        pontoIni = 0;
        pontoFim = 0;
        var arrPontosIntermediarios = [];

        if (idxPontoMaisProximoUsuario > idxPontoMaisProximoOnibus){ // ônibus não passou

            // reconstruir pontos intermediários 
            // (serão usados para forçar a passagem pelos pontos) 
            pontoIni = idxPontoMaisProximoOnibus + 1;
            pontoFim = idxPontoMaisProximoUsuario;

            for (i=pontoIni; i<=pontoFim; i++){
                arrPontosIntermediarios.push({location: L.latLng(arrWaypts[i].getLatLng()), stopover:true});
            }

        } else if (idxPontoMaisProximoUsuario - idxPontoMaisProximoOnibus == 1) { //ônibus está se aproximando
            calcularDistanciaTempo = false;
            msgDistanciaTempo += "O &#244;nibus est&#225; chegando ao ponto " + arrPontosOnibus[idxPontoMaisProximoUsuario].referencia + ".</b>";
        } 
        else if (idxPontoMaisProximoUsuario <= idxPontoMaisProximoOnibus) { //ônibus já passou
            calcularDistanciaTempo = false;
            msgDistanciaTempo += "O &#244;nibus j&#225; passou pelo ponto mais pr&#243;ximo de voc&#234;.<br />";
            msgDistanciaTempo += "Experimente arrastar o marcador para um outro ponto.";
        } 
        
    }
        
    else {
        calcularDistanciaTempo = false;

        if (proximoHorarioSaidaOnibus != "") {
            previsaoSaida = "A sa&#237;da prevista do pr&#243;ximo &#244;nibus ser&#225; &#224;s " +  proximoHorarioSaidaOnibus + " horas.<br />";
        }
        
        if (onibusEstaPontoInicial()){
            msgDistanciaTempo += "O &#244;nibus est&#225; no ponto inicial.<br />" + previsaoSaida;
            msgDistanciaTempo += "A previs&#227;o para chegada ao ponto " + arrPontosOnibus[idxPontoMaisProximoUsuario].referencia + " &#224;s " +arrPontoProxHorario[idxPontoMaisProximoUsuario] + " horas.</b>";
        } if (usuarioEstaPontoInicial()){
            msgDistanciaTempo += "Voc&#234; est&#225; no ponto inicial." + previsaoSaida;
        }
    }

    
        

}