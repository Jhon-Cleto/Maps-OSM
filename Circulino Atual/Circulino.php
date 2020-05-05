
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<link href="inc/mapa.css" rel="stylesheet" type="text/css" />
<link href="inc/circular.css" rel="stylesheet" type="text/css" />
<link href='https://fonts.googleapis.com/css?family=PT+Sans+Narrow' rel='stylesheet' type='text/css'>
<title>Mapa - Pontos Circular Interno</title>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyBgh_o_aN8KgHI3cNTGcyb_CmqdTekOWYs"></script> 


<script type="text/javascript" src="scripts/utf8EncodeDecode.js"></script>
<script type="text/javascript">

		var traceRoute = false;
		var map;
		var directionsService = new google.maps.DirectionsService();
		var directionsDisplay;
		
		var markerBus = new Array;
		var markerIAmHere;
		var arrMarkers = [];
		var arrInfoWindows = [];
		var arrWaypts = [];
		var arrPontoProxHorario = [];
		var arrPontosOnibus = [];

		var coordinates;
		var marker;

		var currentLatOnibus = new Array;
		var currentLngOnibus = new Array;
		var statusCoordinates = new Array;
		var currentVelocOnibus = new Array;
        var lastAddressArray = new Array;
        var lastSendArray = new Array;
		
		var currentLatUsuario = -22.817113;
		var currentLngUsuario = -47.069672;
		var currentVelocOnibus;
		var lastSend = "";
		var lastAddress = "";

		var countCoordsIsNull = 0;

		var proximoHorarioSaidaOnibus = "15:15";

		var idCircularLinha = 1;

		var noturno = false;

		//var isTabActive=true;

		const LINHA_MORADIA = 5;
		const LINHA_NOTURNO = 3;

		

		arrPontoProxHorario.push('15:35');arrPontoProxHorario.push('15:35');arrPontoProxHorario.push('15:35');arrPontoProxHorario.push('15:19');arrPontoProxHorario.push('15:20');arrPontoProxHorario.push('15:20');arrPontoProxHorario.push('15:22');arrPontoProxHorario.push('15:23');arrPontoProxHorario.push('15:23');arrPontoProxHorario.push('15:23');arrPontoProxHorario.push('15:23');arrPontoProxHorario.push('15:25');arrPontoProxHorario.push('15:26');arrPontoProxHorario.push('15:26');arrPontoProxHorario.push('15:27');arrPontoProxHorario.push('15:28');arrPontoProxHorario.push('15:28');arrPontoProxHorario.push('15:30');arrPontoProxHorario.push('15:31');arrPontoProxHorario.push('15:32');arrPontoProxHorario.push('15:32');arrPontoProxHorario.push('15:33');arrPontoProxHorario.push('15:34');arrPontoProxHorario.push('15:36');arrPontoProxHorario.push('15:37');arrPontoProxHorario.push('15:38');arrPontoProxHorario.push('15:16');arrPontoProxHorario.push('15:17');arrPontoProxHorario.push('15:19');arrPontoProxHorario.push('15:19');arrPontoProxHorario.push('15:24');;
		var posicaoPontoInicial = new google.maps.LatLng(-22.828016, -47.060825);		var posicaoCentroUnicamp = new google.maps.LatLng(-22.821677, -47.065283);	
		var posicaoCentroUnicampMoradia = new google.maps.LatLng(-22.819402, -47.073481);		
		$(document).ready(function(){
			if (parent.window['isTabActive']) {
											buscarPosicaoOnibusAjax();
							}
		});

		// recarrega a pagina a cada 3 segundos
		 
					setInterval(function(){
				if (!parent.window['isTabActive']) return;
				
				if (countCoordsIsNull >=10){

					location.reload(true);
					
				} else {

					if (idCircularLinha != LINHA_MORADIA) { buscarPosicaoOnibusAjax();}
					else { buscarPosicaoOnibusAjax();}

					showWhereIsBus();
		
					if (traceRoute){
						route();
					}
				}
				
			}, 3000);
				//}

		// função para buscar a posição do ônibus
		function buscarPosicaoOnibusAjax(){

			var dados = {
					idCircularLinha: 1,
					idCirculino: 5			};
					
			if (idCircularLinha != LINHA_MORADIA) { 
				// linhas internas
				var idCirculino = 5;
				
		        $.ajax({
		        	url : 'https://www.prefeitura.unicamp.br/posicao/site/linha/'+idCircularLinha+'/circulino/'+idCirculino,
		            type : 'GET',
		            dataType: 'json',
		            success: function(data){
	
		            	currentLatOnibus[0] = data.latitude;
		                currentLngOnibus[0] = data.longitude;
		                currentVelocOnibus[0] = data.velocidadeMedia;
		                statusCoordinates[0] = data.status; 
		                lastAddressArray[0] = data.endereco;
		                lastSend = data.ultimoEnvio; 
	
		                if (currentLatOnibus[0] == null || currentLngOnibus[0] == null){
		                	countCoordsIsNull++;
		                } else {
		                	countCoordsIsNull = 0;
		                	putBusMarker(idCircularLinha);
		                }
		            } 
		        });
			} else {
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

				
		// enviando o form
		function submitServico(){
			frm = window.document.form
			frm.action = 'mapaPontosCircular.php'
			frm.submit()
		}

		//função para corrigir arredondamento quando ultimo digito for 5
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

		function getPosFromGPS(){
			var options = {enableHighAccuracy: true, maximumAge: 0};
			navigator.geolocation.getCurrentPosition(setLatLng, error, options);
		}
		
		//função para definir a localizacao
		function setLocation() {

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
		    
		    initialize();
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

		// função para verificar se o ônibus esta no ponto inicial
		function onibusEstaPontoInicial(){
			var posicaoOnibus = new google.maps.LatLng(currentLatOnibus[0], currentLngOnibus[0]);

			// considera-se ainda no ponto distancia de 120 metros do ponto inicial
			return (distanceAtoB(posicaoOnibus, posicaoPontoInicial) <= 120);
		}

		// função para verificar se o usuário esta no ponto inicial
		function usuarioEstaPontoInicial(){
			posicaoUsuario = new google.maps.LatLng(currentLatUsuario, currentLngUsuario);

			// considera-se estar no ponto distancia de 120 metros do ponto inicial
			return (distanceAtoB(posicaoUsuario, posicaoPontoInicial) <= 120);
		}

		// função para atualizar conteudo do div modalContentQualCircular (qual circular pegar?)
		function refreshDivModal(){
			$('#modalContentQualCircular').load('qualCircular.php?currentLatUsuario='+currentLatUsuario+'&currentLngUsuario='+currentLngUsuario);
		}
		
		// função para criar mapa, colocar marcadores e kml
		function initialize() {

			var options = {
					zoom: (idCircularLinha!=LINHA_MORADIA?15:14),
					mapTypeId: google.maps.MapTypeId.ROADMAP,	
					gestureHandling: 'greedy'
			};

			map = new google.maps.Map(document.getElementById("googleMaps"), options);

			var urlKML = "";

 			if (idCircularLinha != LINHA_MORADIA) {
				urlKML = 'https://www.prefeitura.unicamp.br/apps/site/kml/circular/' + 1 + '.kml?rev=5';
 			} else {
 				if (noturno) {
 					opcao = '-noturno';
 				} else {
 					opcao = '-diurno';
 				}

 				urlKML = 'https://www.prefeitura.unicamp.br/apps/site/kml/circular/' + LINHA_MORADIA + opcao + '.kml?rev=5';
 			}

			// traz a rota do circular baseado no arquivo kml do mesmo
			var kmlLayer = new google.maps.KmlLayer({url: urlKML, preserveViewport: true} );
			kmlLayer.setMap(map); // para mostrar a camada no mapa;

			if (idCircularLinha != LINHA_MORADIA) {	map.setCenter(posicaoCentroUnicamp); }
			else {map.setCenter(posicaoCentroUnicampMoradia); }
			
			putBusMarker(1);
			putIAmHereMarker();
		
			// lista os pontos de onibus
			
var infowindow0 = new google.maps.InfoWindow(); infowindow0.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Divisão de Educação Infantil e Complementar </b><br/></td></tr> <tr><td  align="center">Escola Sérgio P. Porto (PONTO INICIAL)</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/semImagem.png" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:15<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:35<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:55<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.827195929759853,-47.06132738254712);
marker0 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Escola Sérgio P. Porto (PONTO INICIAL)' });
google.maps.event.addListener(marker0, 'click', function() {  clearInfoWindow(); infowindow0.open(map,marker0); });arrMarkers.push(marker0);arrInfoWindows.push(infowindow0);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:1, unidade:'Divisão de Educação Infantil e Complementar', referencia:'Escola Sérgio P. Porto (PONTO INICIAL)',  lat:-22.827195929759853, lng: -47.06132738254712};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.827195929759853,-47.06132738254712), stopover:true});

var infowindow1 = new google.maps.InfoWindow(); infowindow1.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Centro de Hematologia e Hemoterapia </b><br/></td></tr> <tr><td  align="center">Hemocentro / FCM</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/1.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:15<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:35<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:55<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.82961372593675,-47.06137016415596);
marker1 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Hemocentro / FCM' });
google.maps.event.addListener(marker1, 'click', function() {  clearInfoWindow(); infowindow1.open(map,marker1); });arrMarkers.push(marker1);arrInfoWindows.push(infowindow1);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:2, unidade:'Centro de Hematologia e Hemoterapia', referencia:'Hemocentro / FCM',  lat:-22.82961372593675, lng: -47.06137016415596};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.82961372593675,-47.06137016415596), stopover:true});

var infowindow2 = new google.maps.InfoWindow(); var pontoOnibus = {ordem:2.5, unidade:'FICTICIO', referencia:'R. Alexandre Fleming',  lat:-22.827718, lng: -47.064633};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.827718,-47.064633), stopover:true});

var infowindow2 = new google.maps.InfoWindow(); infowindow2.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Faculdade de Ciências Médicas </b><br/></td></tr> <tr><td  align="center">CECOM</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/semImagem.png" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:19<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:37<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:56<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.82950983507303,-47.06392931248092);
marker2 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'CECOM' });
google.maps.event.addListener(marker2, 'click', function() {  clearInfoWindow(); infowindow2.open(map,marker2); });arrMarkers.push(marker2);arrInfoWindows.push(infowindow2);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:3, unidade:'Faculdade de Ciências Médicas', referencia:'CECOM',  lat:-22.82950983507303, lng: -47.06392931248092};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.82950983507303,-47.06392931248092), stopover:true});

var infowindow3 = new google.maps.InfoWindow(); infowindow3.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Centro de Atenção Integral à Saúde da Mulher </b><br/></td></tr> <tr><td  align="center">CAISM / Portaria 2 - HC</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/3.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:20<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:38<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:57<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.827839460784613,-47.06677682697773);
marker3 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'CAISM / Portaria 2 - HC' });
google.maps.event.addListener(marker3, 'click', function() {  clearInfoWindow(); infowindow3.open(map,marker3); });arrMarkers.push(marker3);arrInfoWindows.push(infowindow3);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:4, unidade:'Centro de Atenção Integral à Saúde da Mulher', referencia:'CAISM / Portaria 2 - HC',  lat:-22.827839460784613, lng: -47.06677682697773};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.827839460784613,-47.06677682697773), stopover:true});

var infowindow4 = new google.maps.InfoWindow(); infowindow4.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Hospital de Clínicas </b><br/></td></tr> <tr><td  align="center">HC (Portaria F1)</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/semImagem.png" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:20<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:38<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:57<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.82572604482161,-47.06618271768093);
marker4 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'HC (Portaria F1)' });
google.maps.event.addListener(marker4, 'click', function() {  clearInfoWindow(); infowindow4.open(map,marker4); });arrMarkers.push(marker4);arrInfoWindows.push(infowindow4);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:5, unidade:'Hospital de Clínicas', referencia:'HC (Portaria F1)',  lat:-22.82572604482161, lng: -47.06618271768093};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.82572604482161,-47.06618271768093), stopover:true});

var infowindow5 = new google.maps.InfoWindow(); infowindow5.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Centro Para Manutenção de Equipamentos </b><br/></td></tr> <tr><td  align="center">Depto. Saneamento e Ambiente / CEMEQ</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/5.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:22<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:40<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:59<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.824259395268815,-47.06345960497856);
marker5 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Depto. Saneamento e Ambiente / CEMEQ' });
google.maps.event.addListener(marker5, 'click', function() {  clearInfoWindow(); infowindow5.open(map,marker5); });arrMarkers.push(marker5);arrInfoWindows.push(infowindow5);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:6, unidade:'Centro Para Manutenção de Equipamentos', referencia:'Depto. Saneamento e Ambiente / CEMEQ',  lat:-22.824259395268815, lng: -47.06345960497856};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.824259395268815,-47.06345960497856), stopover:true});

var infowindow6 = new google.maps.InfoWindow(); infowindow6.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Coordenadoria de Centros e Núcleos Interdiciplinares de Pesquisa </b><br/></td></tr> <tr><td  align="center">Biotério (CEMIB) / Coleta Seletiva</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/6.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:23<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:41<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 16:00<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td></td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.824342054788538,-47.059839963912964);
marker6 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Biotério (CEMIB) / Coleta Seletiva' });
google.maps.event.addListener(marker6, 'click', function() {  clearInfoWindow(); infowindow6.open(map,marker6); });arrMarkers.push(marker6);arrInfoWindows.push(infowindow6);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:7, unidade:'Coordenadoria de Centros e Núcleos Interdiciplinares de Pesquisa', referencia:'Biotério (CEMIB) / Coleta Seletiva',  lat:-22.824342054788538, lng: -47.059839963912964};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.824342054788538,-47.059839963912964), stopover:true});

var infowindow7 = new google.maps.InfoWindow(); infowindow7.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Prefeitura da Cidade Universitária </b><br/></td></tr> <tr><td  align="center">DMA (Div. Meio Ambiente) / CEMIB (Biotério Central)</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/7.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:23<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:41<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 16:00<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.82339495615896,-47.060167863965034);
marker7 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'DMA (Div. Meio Ambiente) / CEMIB (Biotério Central)' });
google.maps.event.addListener(marker7, 'click', function() {  clearInfoWindow(); infowindow7.open(map,marker7); });arrMarkers.push(marker7);arrInfoWindows.push(infowindow7);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:8, unidade:'Prefeitura da Cidade Universitária', referencia:'DMA (Div. Meio Ambiente) / CEMIB (Biotério Central)',  lat:-22.82339495615896, lng: -47.060167863965034};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.82339495615896,-47.060167863965034), stopover:true});

var infowindow8 = new google.maps.InfoWindow(); var pontoOnibus = {ordem:8.5, unidade:'FICTICIO', referencia:'Av. Cândido Rondon',  lat:-22.821988, lng: -47.061138};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.821988,-47.061138), stopover:true});

var infowindow8 = new google.maps.InfoWindow(); infowindow8.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Gabinete do Reitor </b><br/></td></tr> <tr><td  align="center">Editora da Unicamp / LABEURB</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/8.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:23<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:41<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 16:00<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.823696505309023,-47.061488181352615);
marker8 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Editora da Unicamp / LABEURB' });
google.maps.event.addListener(marker8, 'click', function() {  clearInfoWindow(); infowindow8.open(map,marker8); });arrMarkers.push(marker8);arrInfoWindows.push(infowindow8);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:9, unidade:'Gabinete do Reitor', referencia:'Editora da Unicamp / LABEURB',  lat:-22.823696505309023, lng: -47.061488181352615};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.823696505309023,-47.061488181352615), stopover:true});

var infowindow9 = new google.maps.InfoWindow(); infowindow9.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Coordenadoria de Centros e Núcleos Interdiciplinares de Pesquisa </b><br/></td></tr> <tr><td  align="center">Genética / FEAGRI</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/10.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:25<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:43<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 16:02<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td></td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.819732610271284,-47.06003777682781);
marker9 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Genética / FEAGRI' });
google.maps.event.addListener(marker9, 'click', function() {  clearInfoWindow(); infowindow9.open(map,marker9); });arrMarkers.push(marker9);arrInfoWindows.push(infowindow9);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:10, unidade:'Coordenadoria de Centros e Núcleos Interdiciplinares de Pesquisa', referencia:'Genética / FEAGRI',  lat:-22.819732610271284, lng: -47.06003777682781};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.819732610271284,-47.06003777682781), stopover:true});

var infowindow10 = new google.maps.InfoWindow(); infowindow10.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Coordenadoria de Centros e Núcleos Interdiciplinares de Pesquisa </b><br/></td></tr> <tr><td  align="center">CBMEG / FEAGRI</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/9.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:04<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:26<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:44<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 16:03<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.818471307010142,-47.05960728228092);
marker10 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'CBMEG / FEAGRI' });
google.maps.event.addListener(marker10, 'click', function() {  clearInfoWindow(); infowindow10.open(map,marker10); });arrMarkers.push(marker10);arrInfoWindows.push(infowindow10);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:11, unidade:'Coordenadoria de Centros e Núcleos Interdiciplinares de Pesquisa', referencia:'CBMEG / FEAGRI',  lat:-22.818471307010142, lng: -47.05960728228092};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.818471307010142,-47.05960728228092), stopover:true});

var infowindow11 = new google.maps.InfoWindow(); infowindow11.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Coordenadoria de Centros e Núcleos Interdiciplinares de Pesquisa </b><br/></td></tr> <tr><td  align="center">CEPAGRI / Embrapa</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/11.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:04<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:26<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:44<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 16:03<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td></td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.818031511340962,-47.0604857057333);
marker11 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'CEPAGRI / Embrapa' });
google.maps.event.addListener(marker11, 'click', function() {  clearInfoWindow(); infowindow11.open(map,marker11); });arrMarkers.push(marker11);arrInfoWindows.push(infowindow11);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:12, unidade:'Coordenadoria de Centros e Núcleos Interdiciplinares de Pesquisa', referencia:'CEPAGRI / Embrapa',  lat:-22.818031511340962, lng: -47.0604857057333};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.818031511340962,-47.0604857057333), stopover:true});

var infowindow12 = new google.maps.InfoWindow(); infowindow12.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Centro de Computação </b><br/></td></tr> <tr><td  align="center">CCUEC / CENAPAD</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/12.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:05<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:27<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:45<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.818063107264045,-47.06308342516422);
marker12 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'CCUEC / CENAPAD' });
google.maps.event.addListener(marker12, 'click', function() {  clearInfoWindow(); infowindow12.open(map,marker12); });arrMarkers.push(marker12);arrInfoWindows.push(infowindow12);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:13, unidade:'Centro de Computação', referencia:'CCUEC / CENAPAD',  lat:-22.818063107264045, lng: -47.06308342516422};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.818063107264045,-47.06308342516422), stopover:true});

var infowindow13 = new google.maps.InfoWindow(); infowindow13.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Faculdade de Engenharia Mecânica </b><br/></td></tr> <tr><td  align="center">Faculdade de Engenharia Mecânica</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/14.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:06<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:28<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:46<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td></td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.81851763367064,-47.06610158085823);
marker13 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Faculdade de Engenharia Mecânica' });
google.maps.event.addListener(marker13, 'click', function() {  clearInfoWindow(); infowindow13.open(map,marker13); });arrMarkers.push(marker13);arrInfoWindows.push(infowindow13);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:14, unidade:'Faculdade de Engenharia Mecânica', referencia:'Faculdade de Engenharia Mecânica',  lat:-22.81851763367064, lng: -47.06610158085823};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.81851763367064,-47.06610158085823), stopover:true});

var infowindow14 = new google.maps.InfoWindow(); infowindow14.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Faculdade de Educação </b><br/></td></tr> <tr><td  align="center">FE / IFGW</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/15.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:06<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:28<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:46<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.816911924943703,-47.06609085202217);
marker14 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'FE / IFGW' });
google.maps.event.addListener(marker14, 'click', function() {  clearInfoWindow(); infowindow14.open(map,marker14); });arrMarkers.push(marker14);arrInfoWindows.push(infowindow14);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:15, unidade:'Faculdade de Educação', referencia:'FE / IFGW',  lat:-22.816911924943703, lng: -47.06609085202217};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.816911924943703,-47.06609085202217), stopover:true});

var infowindow15 = new google.maps.InfoWindow(); infowindow15.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Instituto de Economia </b><br/></td></tr> <tr><td  align="center">IE / IMECC</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/16.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:07<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:30<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:47<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.8149687910257,-47.06696391105652);
marker15 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'IE / IMECC' });
google.maps.event.addListener(marker15, 'click', function() {  clearInfoWindow(); infowindow15.open(map,marker15); });arrMarkers.push(marker15);arrInfoWindows.push(infowindow15);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:16, unidade:'Instituto de Economia', referencia:'IE / IMECC',  lat:-22.8149687910257, lng: -47.06696391105652};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.8149687910257,-47.06696391105652), stopover:true});

var infowindow16 = new google.maps.InfoWindow(); infowindow16.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Associação de Docentes da UNICAMP </b><br/></td></tr> <tr><td  align="center">ADunicamp/STU</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/17.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:08<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:31<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:48<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.81348716556454,-47.065243273973465);
marker16 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'ADunicamp/STU' });
google.maps.event.addListener(marker16, 'click', function() {  clearInfoWindow(); infowindow16.open(map,marker16); });arrMarkers.push(marker16);arrInfoWindows.push(infowindow16);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:17, unidade:'Associação de Docentes da UNICAMP', referencia:'ADunicamp/STU',  lat:-22.81348716556454, lng: -47.065243273973465};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.81348716556454,-47.065243273973465), stopover:true});

var infowindow17 = new google.maps.InfoWindow(); infowindow17.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Fundação de Desenvolvimento da UNICAMP </b><br/></td></tr> <tr><td  align="center">Funcamp / COMVEST</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/18.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:09<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:32<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:49<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.812519511209437,-47.06756003201008);
marker17 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Funcamp / COMVEST' });
google.maps.event.addListener(marker17, 'click', function() {  clearInfoWindow(); infowindow17.open(map,marker17); });arrMarkers.push(marker17);arrInfoWindows.push(infowindow17);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:18, unidade:'Fundação de Desenvolvimento da UNICAMP', referencia:'Funcamp / COMVEST',  lat:-22.812519511209437, lng: -47.06756003201008};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.812519511209437,-47.06756003201008), stopover:true});

var infowindow18 = new google.maps.InfoWindow(); infowindow18.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Pró-Reitoria de Extensão e Assuntos Comunitários </b><br/></td></tr> <tr><td  align="center">Casa do Lago / IG (Instituto de Geociências)</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/19.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:10<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:32<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:50<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.812994160952606,-47.06950530409813);
marker18 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Casa do Lago / IG (Instituto de Geociências)' });
google.maps.event.addListener(marker18, 'click', function() {  clearInfoWindow(); infowindow18.open(map,marker18); });arrMarkers.push(marker18);arrInfoWindows.push(infowindow18);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:19, unidade:'Pró-Reitoria de Extensão e Assuntos Comunitários', referencia:'Casa do Lago / IG (Instituto de Geociências)',  lat:-22.812994160952606, lng: -47.06950530409813};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.812994160952606,-47.06950530409813), stopover:true});

var infowindow19 = new google.maps.InfoWindow(); infowindow19.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Faculdade de Educação Física </b><br/></td></tr> <tr><td  align="center">FEF / Centro de Convenções</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/20.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:11<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:33<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:50<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.81423669359395,-47.07185357809061);
marker19 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'FEF / Centro de Convenções' });
google.maps.event.addListener(marker19, 'click', function() {  clearInfoWindow(); infowindow19.open(map,marker19); });arrMarkers.push(marker19);arrInfoWindows.push(infowindow19);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:20, unidade:'Faculdade de Educação Física', referencia:'FEF / Centro de Convenções',  lat:-22.81423669359395, lng: -47.07185357809061};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.81423669359395,-47.07185357809061), stopover:true});

var infowindow20 = new google.maps.InfoWindow(); infowindow20.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Faculdade de Educação Física </b><br/></td></tr> <tr><td  align="center">FEF / Restaurante Universitário (RU)</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/21.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:12<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:34<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:52<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.81654917375286,-47.07285739481449);
marker20 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'FEF / Restaurante Universitário (RU)' });
google.maps.event.addListener(marker20, 'click', function() {  clearInfoWindow(); infowindow20.open(map,marker20); });arrMarkers.push(marker20);arrInfoWindows.push(infowindow20);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:21, unidade:'Faculdade de Educação Física', referencia:'FEF / Restaurante Universitário (RU)',  lat:-22.81654917375286, lng: -47.07285739481449};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.81654917375286,-47.07285739481449), stopover:true});

var infowindow21 = new google.maps.InfoWindow(); infowindow21.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Instituto de Biologia </b><br/></td></tr> <tr><td  align="center">IB / Praça Henfil</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/22.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:13<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:36<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:53<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.819294695494193,-47.07241617143154);
marker21 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'IB / Praça Henfil' });
google.maps.event.addListener(marker21, 'click', function() {  clearInfoWindow(); infowindow21.open(map,marker21); });arrMarkers.push(marker21);arrInfoWindows.push(infowindow21);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:22, unidade:'Instituto de Biologia', referencia:'IB / Praça Henfil',  lat:-22.819294695494193, lng: -47.07241617143154};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.819294695494193,-47.07241617143154), stopover:true});

var infowindow22 = new google.maps.InfoWindow(); infowindow22.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Instituto de Biologia </b><br/></td></tr> <tr><td  align="center">IB / Praça Carlos Drumond de Andrade</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/23.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:14<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:37<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:53<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td></td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.82167567621637,-47.070336788892746);
marker22 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'IB / Praça Carlos Drumond de Andrade' });
google.maps.event.addListener(marker22, 'click', function() {  clearInfoWindow(); infowindow22.open(map,marker22); });arrMarkers.push(marker22);arrInfoWindows.push(infowindow22);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:23, unidade:'Instituto de Biologia', referencia:'IB / Praça Carlos Drumond de Andrade',  lat:-22.82167567621637, lng: -47.070336788892746};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.82167567621637,-47.070336788892746), stopover:true});

var infowindow23 = new google.maps.InfoWindow(); infowindow23.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Gabinete do Reitor </b><br/></td></tr> <tr><td  align="center">Reitoria I / Prefeitura</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/24.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:15<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:38<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:55<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.825048660966733,-47.067298516631126);
marker23 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Reitoria I / Prefeitura' });
google.maps.event.addListener(marker23, 'click', function() {  clearInfoWindow(); infowindow23.open(map,marker23); });arrMarkers.push(marker23);arrInfoWindows.push(infowindow23);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:24, unidade:'Gabinete do Reitor', referencia:'Reitoria I / Prefeitura',  lat:-22.825048660966733, lng: -47.067298516631126};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.825048660966733,-47.067298516631126), stopover:true});

var infowindow24 = new google.maps.InfoWindow(); infowindow24.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Centro de Atenção Integral à Saúde da Mulher </b><br/></td></tr> <tr><td  align="center">Sobrapar</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/25.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:16<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:39<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:56<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.827383217435795,-47.067575454711914);
marker24 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Sobrapar' });
google.maps.event.addListener(marker24, 'click', function() {  clearInfoWindow(); infowindow24.open(map,marker24); });arrMarkers.push(marker24);arrInfoWindows.push(infowindow24);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:25, unidade:'Centro de Atenção Integral à Saúde da Mulher', referencia:'Sobrapar',  lat:-22.827383217435795, lng: -47.067575454711914};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.827383217435795,-47.067575454711914), stopover:true});

var infowindow25 = new google.maps.InfoWindow(); infowindow25.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Hospital de Clínicas </b><br/></td></tr> <tr><td  align="center">HC / Área da Saúde</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/46.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:17<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:40<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:57<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.82880780070171,-47.066226303577366);
marker25 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'HC / Área da Saúde' });
google.maps.event.addListener(marker25, 'click', function() {  clearInfoWindow(); infowindow25.open(map,marker25); });arrMarkers.push(marker25);arrInfoWindows.push(infowindow25);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:26, unidade:'Hospital de Clínicas', referencia:'HC / Área da Saúde',  lat:-22.82880780070171, lng: -47.066226303577366};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.82880780070171,-47.066226303577366), stopover:true});

var infowindow26 = new google.maps.InfoWindow(); infowindow26.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Faculdade de Enfermagem </b><br/></td></tr> <tr><td  align="center">FENF /CIPED</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/semImagem.png" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:19<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:42<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:59<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td></td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.830572518710206,-47.06210911273956);
marker26 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'FENF /CIPED' });
google.maps.event.addListener(marker26, 'click', function() {  clearInfoWindow(); infowindow26.open(map,marker26); });arrMarkers.push(marker26);arrInfoWindows.push(infowindow26);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:27, unidade:'Faculdade de Enfermagem', referencia:'FENF /CIPED',  lat:-22.830572518710206, lng: -47.06210911273956};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.830572518710206,-47.06210911273956), stopover:true});

var infowindow27 = new google.maps.InfoWindow(); infowindow27.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Faculdade de Ciências Médicas </b><br/></td></tr> <tr><td  align="center">FCM / Hemocentro</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/27.JPG" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:19<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:44<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:59<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td></td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.82971443480271,-47.06070229411125);
marker27 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'FCM / Hemocentro' });
google.maps.event.addListener(marker27, 'click', function() {  clearInfoWindow(); infowindow27.open(map,marker27); });arrMarkers.push(marker27);arrInfoWindows.push(infowindow27);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:28, unidade:'Faculdade de Ciências Médicas', referencia:'FCM / Hemocentro',  lat:-22.82971443480271, lng: -47.06070229411125};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.82971443480271,-47.06070229411125), stopover:true});

var infowindow28 = new google.maps.InfoWindow(); infowindow28.setContent('<table border="0" width="350"><tr><td colspan="2" align="center"><b>Divisão de Educação Infantil e Complementar </b><br/></td></tr> <tr><td  align="center">Escola Sérgio P. Porto (PONTO FINAL)</td></tr> </tr> <tr><td  align="center"><img src="img/fotosPontosCI/semImagem.png" style="max-width: 80%; max-height: 80%;"></td></tr> <tr><td  align="center"><b>Horários do Circular 1 (sentido anti-horário)</b></td></tr> <tr><td>15:24<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 15:45<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"> - 16:02<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></td></tr> <tr><td>Este ponto possui cobertura.</td></tr> <tr><td><img src=img/cadeirante.jpg style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com ônibus adaptado para deficientes físicos.</font></td></tr> </table>');coordinates = new google.maps.LatLng(-22.82711228712918,-47.06136914397621);
marker28 = new google.maps.Marker({ position: coordinates, map: map, icon: 'img/marcadoresPontosCI/buildings.png', title: 'Escola Sérgio P. Porto (PONTO FINAL)' });
google.maps.event.addListener(marker28, 'click', function() {  clearInfoWindow(); infowindow28.open(map,marker28); });arrMarkers.push(marker28);arrInfoWindows.push(infowindow28);google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });var pontoOnibus = {ordem:29, unidade:'Divisão de Educação Infantil e Complementar', referencia:'Escola Sérgio P. Porto (PONTO FINAL)',  lat:-22.82711228712918, lng: -47.06136914397621};
arrPontosOnibus.push(pontoOnibus);
arrWaypts.push({location: new google.maps.LatLng(-22.82711228712918,-47.06136914397621), stopover:true});

		}
						

		//função para colocar o marcador o ônibus
		function putBusMarker(linha) { 

			for (var i = 0; i< currentLatOnibus.length; i++){
				//remove o marcador do mapa
				if (markerBus[i] != null) {
					markerBus[i].setMap(null);
				}
				
		
				//cria o marcador e o adiona ao mapa	
				if (map != null) {
					markerBus[i] = new google.maps.Marker({	map: map,
									  						zIndex: google.maps.Marker.MAX_ZINDEX + 1,
									  						position: new google.maps.LatLng(currentLatOnibus[i], currentLngOnibus[i]),
									  						draggable: false
								      			  	  });

				if (statusCoordinates[i] == 1){
					  markerBus[i].setIcon('img/marcadoresPontosCI/bus.png');
				} else if (statusCoordinates[i] == 2){
					  markerBus[i].setIcon('img/marcadoresPontosCI/busEstimatePos.png');
				} else if (statusCoordinates[i] == 3){
					  markerBus[i].setIcon('img/marcadoresPontosCI/busNoConnection.png');
				}
				 
				}

			}
				
			
			var centralizarNoOnibus = document.getElementById("chkCentralizarNoOnibus");
			
			// centralizar o mapa
			if (centralizarNoOnibus.checked){		
				if (!isNaN(markerBus[0].position.lat()) && !isNaN(markerBus[0].position.lng())){
					if (linha != LINHA_MORADIA) {map.setCenter(markerBus[0].position);}
					else {}
				}
			}
 		}

		
		//função para colocar o marcador posição do usuário
		function putIAmHereMarker() { 

			//remove o marcador do mapa
			if (markerIAmHere != null) {
				markerIAmHere.setMap(null);
			}

			if (map != null) {
				//cria o marcador e o adiona ao mapa			
				markerIAmHere = new google.maps.Marker({    map: map,
									  title: 'Arraste o marcador para indicar onde você está',
									  icon: 'img/marcadoresPontosCI/iamhere.png',
									  position: new google.maps.LatLng(currentLatUsuario, currentLngUsuario),
									  draggable: true,
									  zIndex: google.maps.Marker.MAX_ZINDEX + 1
								      });
				
				//adiciona evento ao marcador recem criado
				google.maps.event.addListener(markerIAmHere, 'click', function() {
												info.setContent('Voc&#234; est&#225; aqui.');
												info.open(map, markerIAmHere);
												});
	
				//adiciona evento ao marcador recem criado
				google.maps.event.addListener(markerIAmHere, 'dragend', function() {
													  //atualiza coordenadas correntes
													  currentLatUsuario = markerIAmHere.position.lat();
													  currentLngUsuario = markerIAmHere.position.lng();
													  refreshDivModal();
												});

				markerIAmHere.setMap(map);

				refreshDivModal();

			}
		}

		// funcao para limpar os baloes de informacoes abertos
		function clearInfoWindow() {
			for (i=0; i<arrInfoWindows.length; i++){
					arrInfoWindows[i].close();
			}
		}

		// função para mostrar/ocultar marcadores e ponto do usuário
		function setVisibleMarkers(){

			var value;
			
			if (map.getZoom() < 15) {
				value = false;
			} else {
				value = true;
			}
			
			for (i=0; i<arrMarkers.length; i++) {
				arrMarkers[i].setVisible(value);
			}

			markerIAmHere.setVisible(value);
		}

		//função para retornar a distancia em metros entre A e B
		function distanceAtoB(pointA, pointB){
			
			latOrigem = pointA.lat(); 
			lngOrigem = pointA.lng();

			latDestino = pointB.lat();
			lngDestino = pointB.lng();

			
			distancia = 6371000*Math.acos(Math.cos(Math.PI*(90-latDestino)/180)*Math.cos((90-latOrigem)*Math.PI/180)+Math.sin((90-latDestino)*Math.PI/180)*Math.sin((90-latOrigem)*Math.PI/180)*Math.cos((lngOrigem-lngDestino)*Math.PI/180));

			return distancia;
		}
		
		//função para traz a rota e exibir distancia e tempo
		function route(){  

			traceRoute = true;
			
			currentLatOnibus[0] = markerBus[0].position.lat();
		    currentLngOnibus[0] = markerBus[0].position.lng();

			calcularDistanciaTempo = true;
			msgDistanciaTempo = "<span style=\"font-weight: bold\">";
			
			//verificando o ponto mais proximo do usuário e distancia do usuario ao ponto
			idxPontoMaisProximoUsuario=0;
			usuarioPosicao = new google.maps.LatLng(markerIAmHere.position.lat(), markerIAmHere.position.lng()); 
			distanciaUsuarioAPonto = 9999999999;

			for (i=0; i<arrPontosOnibus.length; i++){
				if (arrPontosOnibus[i].unidade != 'FICTICIO'){
					busStop = new google.maps.LatLng(arrPontosOnibus[i].lat, arrPontosOnibus[i].lng);
					distanceTmp = distanceAtoB(busStop, usuarioPosicao);
	
					if (distanceTmp < distanciaUsuarioAPonto) {
						idxPontoMaisProximoUsuario = i;
						distanciaUsuarioAPonto = distanceTmp;
					}
				}
			}

			// setando o ponto de ônibus mais proximo ao usuário
			pontoMaisProximoUsuario = new google.maps.LatLng(arrPontosOnibus[idxPontoMaisProximoUsuario].lat, arrPontosOnibus[idxPontoMaisProximoUsuario].lng);
			
			if (!onibusEstaPontoInicial() && !usuarioEstaPontoInicial()){

				//verificando o ponto mais proximo do ônibus e a sua distancia do ônibus ao ponto.
				idxPontoMaisProximoOnibus=0;
				onibusPosicao = new google.maps.LatLng(markerBus[0].position.lat(), markerBus[0].position.lng()); 
				distanciaOnibusAPonto = 9999999999;

				for (i=0; i<arrPontosOnibus.length; i++){
					if (arrPontosOnibus[i].unidade != 'FICTICIO'){
						busStop = new google.maps.LatLng(arrPontosOnibus[i].lat, arrPontosOnibus[i].lng);
						distanceTmp = distanceAtoB(busStop, onibusPosicao);

						if (distanceTmp < distanciaOnibusAPonto) {
							if (i > idxPontoMaisProximoOnibus /*&&  (i - idxPontoMaisProximoOnibus) <= 10*/) { // para evitar pegar pontos fora da sequencia
								idxPontoMaisProximoOnibus = i;
								distanciaOnibusAPonto = distanceTmp;
							}
						}
					}
				}

				// setando o ponto de ônibus mais proximo do ônibus
				pontoMaisProximoOnibus = new google.maps.LatLng(arrPontosOnibus[idxPontoMaisProximoOnibus].lat, arrPontosOnibus[idxPontoMaisProximoOnibus].lng);

				pontoIni = 0;
				pontoFim = 0;
				var arrPontosIntermediarios = [];
				
				if (idxPontoMaisProximoUsuario > idxPontoMaisProximoOnibus){ // ônibus nao passou

					// reconstruir pontos intermediarios 
					// (serao usados para forcar a passagem pelos pontos) 
					pontoIni = idxPontoMaisProximoOnibus + 1;
					pontoFim = idxPontoMaisProximoUsuario;
	
					for (i=pontoIni; i<=pontoFim; i++){
						arrPontosIntermediarios.push({location: new google.maps.LatLng(arrWaypts[i].location.lat(), arrWaypts[i].location.lng()), stopover:true});
					}

				} else if (idxPontoMaisProximoUsuario - idxPontoMaisProximoOnibus == 1) { //ônibus esta se aproximando
					calcularDistanciaTempo = false;
					msgDistanciaTempo += "O &#244;nibus est&#225; chegando ao ponto " + arrPontosOnibus[idxPontoMaisProximoUsuario].referencia + ".</b>";
				} 
				else if (idxPontoMaisProximoUsuario <= idxPontoMaisProximoOnibus) { //ônibus ja passou
					calcularDistanciaTempo = false;
					msgDistanciaTempo += "O &#244;nibus j&#225; passou pelo ponto mais pr&#243;ximo de voc&#234;.<br />";
					msgDistanciaTempo += "Experimente arrastar o marcador para um outro ponto.";
				} 
			} else {
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
			
			// limpar trajeto
			if (directionsDisplay != null){
				directionsDisplay.setMap(null);
				directionsDisplay = null;
			}
			
			if (calcularDistanciaTempo) {

				var DrivingOptions = {
	    			trafficModel: 'pessimistic'
	    		};
	
				var request = {
						origin: onibusPosicao, //pontoMaisProximoOnibus,
						destination: pontoMaisProximoUsuario, 
						transitOptions: {
						    modes: [google.maps.TransitMode.BUS]
						  },
						waypoints: arrPontosIntermediarios,
						provideRouteAlternatives: false,
						drivingOptions: {
			                departureTime: new Date (Date.Now),
			                trafficModel: google.maps.TrafficModel.BEST_GUESS
			            },
						travelMode: google.maps.DirectionsTravelMode.DRIVING,
						durationInTraffic: true
				};

	
				directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true,
																		preserveViewport: true, 
																		polylineOptions: {strokeColor: "red"}
																	   });
				
				directionsService.route(request, function(response, status) {
						
						if (status == google.maps.DirectionsStatus.OK) {

						    var myroute = response.routes[0];
				            var dist = 0;
				            var duration = 0;
					            
				            for (i = 0; i < myroute.legs.length; i++) {
				            	dist += myroute.legs[i].distance.value;
				            	duration += myroute.legs[i].duration.value;
				            }
	
				            dist = dist / 1000;
				            dist = dist + fixRounding(dist);	//corrigindo o arredondamento
						    dist = dist.toFixed(2);


						    duration = Math.ceil(duration / 60);
						    
				            //mostrar a rota calculada
							directionsDisplay.setDirections(response);

							msgDistanciaTempo += '<span style="font-weight: bold">A dist&#226;ncia do &#244;nibus at&#233; o ponto mais pr&#243;ximo a voc&#234; &#233; <br/>' + dist +
				            ' km com previs&#227;o de chegada em ' + duration + ' minutos.</span>';

							document.getElementById("details").innerHTML = msgDistanciaTempo;
							
							directionsDisplay.setMap(map);
											
						} else {
							msgDistanciaTempo += "Desculpe, n&#227;o foi poss&#237;vel calcular a dist&#226;ncia e o tempo.";
						}
				});
			} else {
				document.getElementById("details").innerHTML = msgDistanciaTempo;
			}

			msgDistanciaTempo += "</span>";
			
			clearInfoWindow();

		}

		//função para formatar hora
		function formatTime(secs){
		   var times = new Array(3600, 60, 1);
		   var time = '';
		   var tmp;
		   for(var i = 0; i < times.length; i++){
			  tmp = Math.floor(secs / times[i]);
			  if(tmp < 1){
				 tmp = '00';
			  }
			  else if(tmp < 10){
				 tmp = '0' + tmp;
			  }
			  time += tmp;
			  if(i < 2){
				 time += ':';
			  }
			  secs = secs % times[i];
		   }
		   return time;
		}

		function showWhereIsBus(){
			
			if (idCircularLinha != LINHA_MORADIA && statusCoordinates[0] != 3) {
				var msg = "";
	
	            msg ='<span style="font-weight: bold">Atualmente o &#244;nibus est&#225; em ' + lastAddressArray[0];
	            
	            if (onibusEstaPontoInicial() && lastAddress.indexOf("Sabin") != -1){
	            	msg += " (ponto inicial).";
	            }
	
	            msg += '</span>';
		        msg += '<br/><span style="font-weight: bold">A velocidade média é ' + currentVelocOnibus[0].toString() + ' km/h.</span>';
	
		        document.getElementById("endereco").innerHTML = msg;  
			}

		}
	</script>
</head>

<body id="mainPage" onload="setLocation();">
<div id="divInative" name="divInative" style="position: absolute;
    width: 415px;
    height: 170px;
    z-index: 15;
    top: 50%;
    left: 50%;
    margin: -100px 0px 0px -150px;
    background: rgb(222, 222, 222)   none repeat scroll 0% 0%;
    text-align: center;
    border: medium solid;
    display: none;
    border: 1px solid rgba(0, 0, 0, 0.12);
    border-radius: 5px;">
  <h1>Atualiza&#231;&#227;o pausada<br />Clique para retornar <br/><img src="img/btn_play.png" width="20%" height="20%"></h1>
</div>
	<br />
	
	<table width="100%">
		<tbody>
			<tr>
				<td>	
					<p class="mapa_texto">
						<span style="color: #b45938; font-size: 18px; font-weight: bold;">
							Instru&#231;&#245;es gerais </span> <br /> <br /> - Selecione a linha desejada
						para carregar o itiner&#225;rio (Circular I, Circular II via FEC, Circular
						II via Museu ou Circular Noturno) <br /> - Clique sobre o ponto
						desejado para obter os hor&#225;rios previstos de passagem dos &#244;nibus nos
						pr&#243;ximos 60 minutos <br /> - Clique no bot&#227;o tra&#231;ar rota para ver a
						dist&#226;ncia entre o &#244;nibus e o ponto mais pr&#243;ximo de voc&#234; 
						<br> - Para rela&#231;&#227;o geral dos hor&#225;rios das linhas, acesse a p&#225;gina da 
						<a href="https://www.prefeitura.unicamp.br/servicos/diretoria-de-servicos-de-transporte#circular">Unitransp</a>
						<br /> 
<!--
						<span
							style="color: #FF0000; font-size: 15px; font-weight: bold; text-decoration: underline;">ATEN&#199;&#195;O:
							A localiza&#231;&#227;o em tempo real dos &#244;nibus circulares internos encontra-se em car&#225;ter experimental
							<br /> e podem existir imprecis&#245;es. 
						</span>
	-->					
						<br /> <br />
						<span font-size: 15px;">
							Esta funcionalidade foi desenvolvida dentro do <a href="http://smartcampus.prefeitura.unicamp.br/" target="_blank">Projeto SmartCampus.</a>
						</span>
						
						<br />
					</p>					
				</td>
				<td>	
					<a href="http://smartcampus.prefeitura.unicamp.br/" target="_blank">
						<img src="https://www.prefeitura.unicamp.br/imagens/estrutura/smart-campus-site.png"  alt="Projeto Smart Campus" title="Projeto Smart Campus" style="float:right;position:relative;margin-top: 0px; margin-right:130PX">
					</a>
						
				</td>
			</tr>
		</tbody>
	</table>
	
	<div id="container">
		<div id="googleMaps"
			style="float: left; margin-left: 0px; height: 600px; width: 630px;">
		</div>

		<div style="float: left; margin-left: 0px; height: 600px; width: 30%;">

			<form id="form" name="form" action="/site/mapaPontosCircular.php"
				method="POST">
				<input type="hidden" id="hidMontarFiltroTipoLinha" name="hidMontarFiltroTipoLinha"
					value="">
				<input type="hidden" id="hidTempFiltroTipoLinha" name="hidTempFiltroTipoLinha"
					value="YTozOntpOjA7YTozOntzOjU6ImxpbmhhIjtzOjE6IjEiO3M6OToiY2lyY3VsaW5vIjtzOjE6IjUiO3M6NToiZGVzY3IiO3M6MTI1OiJDaXJjdWxhciAxIChzZW50aWRvIGFudGktaG9y4XJpbykgLSDUbmlidXMgMTxpbWcgc3JjPWltZy9jYWRlaXJhbnRlLmpwZyBzdHlsZT0id2lkdGg6IDEzcHg7IGhlaWdodDogMTNweDsgbWFyZ2luLWxlZnQ6IDNweDsiPiI7fWk6MTthOjM6e3M6NToibGluaGEiO3M6MToiMiI7czo5OiJjaXJjdWxpbm8iO3M6MToiNiI7czo1OiJkZXNjciI7czoxMTk6IkNpcmN1bGFyIDIgLSB2aWEgRkVDIChzZW50aWRvIGhvcuFyaW8pPGltZyBzcmM9aW1nL2NhZGVpcmFudGUuanBnIHN0eWxlPSJ3aWR0aDogMTNweDsgaGVpZ2h0OiAxM3B4OyBtYXJnaW4tbGVmdDogM3B4OyI+Ijt9aToyO2E6Mzp7czo1OiJsaW5oYSI7aTo1O3M6OToiY2lyY3VsaW5vIjtzOjE6IjAiO3M6NToiZGVzY3IiO3M6MTQ6ItRuaWJ1cyBNb3JhZGlhIjt9fQ==">										
				<div id="controls1"
					style="width: 280px; height: auto; float: left; margin-left: 25px;">
					<strong class="mapa_titulo_2">Estou em</strong><br /> <input
						type="radio" name="myLocal" id="myLocal" onclick="setLocation();"
						value="0" CHECKED>Marcador
					(arraste o marcador para indicar sua posição) <br /> <input
						type="radio" name="myLocal" id="myLocal" onclick="setLocation();"
						value="1" >Minha
					localiza&#231;&#227;o (apenas para dispositivos com GPS) <br /> <br />
					<strong class="mapa_titulo_2">Tipo de Linha</strong><br /> 
					<input type="radio" onchange="submitServico()" id="tipoLinha" name="tipoLinha" value="1;5" checked><span style="font-weight: bold; color: #0097C9">Circular 1 (sentido anti-horário) - Ônibus 1<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></span><br><input type="radio" onchange="submitServico()" id="tipoLinha" name="tipoLinha" value="2;6" ><span style="font-weight: bold; color: #7C7373">Circular 2 - via FEC (sentido horário)<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></span><br><input type="radio" onchange="submitServico()" id="tipoLinha" name="tipoLinha" value="5;0" ><span style="font-weight: bold; color: #1267FF">Ônibus Moradia</span><br>					<br /> <img src="img/cadeirante.jpg"
						style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens
						com &#244;nibus adaptado para deficientes f&#237;sicos</font> <br /> <br />
					<div id="rotasMoradia" style="display: none">
						Rotas especiais Moradia: <br />
						<div id="legendaDiurno" style="display: inline">
                        			<img src="img/rotaAmarelo.png">&#193;rea da Sa&#250;de (#)<br />
                        		</div>
                        		<div id="legendaNoturno" style="display: none">
                            			<img src="img/rotaRoxo.png">Moradia via Terminal Bar&#227;o (B)<br />
                            			<img src="img/rotaVerde.png">Moradia via Centro M&#233;dico (C)<br />
                        		</div>
                        		<br /><br /><br />
					</div>
					<div id="divOptions" style="display: inline">
    					<strong class="mapa_titulo_2">Opções</strong><br />
    					<input type="checkbox" id="chkCentralizarNoOnibus">Centralizar no ônibus
    					<br /><br /><br />
					</div>
											<a href="javascript:route();" id="tracarRota" style="""padding-left:4em\">
							<img src="img/btn_tracar_rota.png">
						</a> <br /><br />
						<a id="qualCircular" style="""padding-left:4em\">
							<img src="img/btn_qual_circular.png">
						</a>			
						<br />
						<div id="details" class="mapa_texto"
							style="width: 280px; height: 80px; float: left; margin-left: 10px; margin-top: 20px;">
						</div>
						<strong class="mapa_titulo_2">Legenda</strong><br />
						<br /> <img src="img/marcadoresPontosCI/bus.png"> - Posição real<br />
						<img src="img/marcadoresPontosCI/busEstimatePos.png"> - Posição
						estimada devido perda momentânea de sinal<br /> <img
							src="img/marcadoresPontosCI/busNoConnection.png"> - Perda
						prolongada de sinal<br />
									</div>
			</form>
		</div>
	</div>
	<div id="resultado"></div>
	<div id="endereco" class="mapa_texto"
		style="float: left; margin-left: 0px; height: 50px; width: 630px;">
	</div>
	

	<div id="myModal" class="modal" style="width: 100%;">
	    <div class="modal-content" style="width: 75%;">
		    <span class="close">&times;</span>
		    <script>refreshDivModal();</script>
		    <div id="modalContentQualCircular">
		    	

	<form method="post" name="frmBusca" id="frmBusca" ajax="true">
	
	<table width="80%" border="0">
	<tr>
	<td width="50%">
			<strong style="font-size: 14px;">Onde você está?: (abaixo 3 pontos próximos a você)</strong><br />
					<div id="pontoOrigem">
					<select name="selPontoOrigem"	class="select_circular_ponto"><option value="65">Museu Exploratório de Ciências</option><option value="77">FACAMP</option><option value="85">Instituto Eldorado</option></select>					</div>
	</td>
		<td width="50%">
			<strong style="font-size: 14px;">Onde deseja ir?:</strong><br />
					<div id="pontoDestino">
					<select name="selPontoDestino" class="select_circular_ponto"><option value="84">ADunicamp (Associação de Docentes da Unicamp) </option><option value="17">ADunicamp/STU</option><option value="6">Biotério (CEMIB) / Coleta Seletiva</option><option value="3">CAISM / Portaria 2 - HC</option><option value="19">Casa do Lago / IG (Instituto de Geociências)</option><option value="9">CBMEG / FEAGRI</option><option value="12">CCUEC / CENAPAD</option><option value="63">CCUEC / Vigilância</option><option value="60">CECI Berçário / FEA</option><option value="80">CECOM</option><option value="54">CEL (Centro de Ensino de Línguas) / IG</option><option value="71">CEMEQ / Banco do Brasil</option><option value="52">Centro de Convenções</option><option value="11">CEPAGRI / Embrapa</option><option value="55">CEPETRO / Funcamp</option><option value="5">Depto. Saneamento e Ambiente / CEMEQ</option><option value="72">DGA / Praça das Bandeiras</option><option value="7">DMA (Div. Meio Ambiente) / CEMIB (Biotério Central)</option><option value="8">Editora da Unicamp / LABEURB</option><option value="79">Escola Sérgio P. Porto (PONTO FINAL)</option><option value="78">Escola Sérgio P. Porto (PONTO INICIAL)</option><option value="77">FACAMP</option><option value="74">Faculdade de Ciências Farmacêuticas</option><option value="14">Faculdade de Engenharia Mecânica</option><option value="27">FCM / Hemocentro</option><option value="15">FE / IFGW</option><option value="61">FEA / Praça da Paz</option><option value="69">FEAGRI / CBMEG</option><option value="67">FEAGRI / CCUEC (Centro de Computação)</option><option value="68">FEAGRI / Embrapa</option><option value="70">FEAGRI / Genética (CMBEG)</option><option value="66">FEC / RS (Restaurante da Saturnino)</option><option value="20">FEF / Centro de Convenções</option><option value="21">FEF / Restaurante Universitário (RU)</option><option value="76">FENF /CIPED</option><option value="62">FEQ / FEM</option><option value="18">Funcamp / COMVEST</option><option value="10">Genética / FEAGRI</option><option value="4">HC (Portaria F1)</option><option value="46">HC / Área da Saúde</option><option value="1">Hemocentro / FCM</option><option value="23">IB / Praça Carlos Drumond de Andrade</option><option value="22">IB / Praça Henfil</option><option value="50">IB / SIARQ (Arquivo Central do Sistema de Arquivos)</option><option value="49">IB / Zoologia</option><option value="56">IC / Pavilhão do IA (Instituto de Artes)</option><option value="57">IE / FE</option><option value="16">IE / IMECC</option><option value="53">IEL / Correios</option><option value="58">IFGW / FE</option><option value="73">IMECC / IE</option><option value="85">Instituto Eldorado</option><option value="59">IQ / FEA</option><option value="65">Museu Exploratório de Ciências</option><option value="64">NEPP / IC (Instituto de Computação)</option><option value="24">Reitoria I / Prefeitura</option><option value="47">Reitoria II / GGBS</option><option value="51">RU / BCCL (Biblioteca Central Cesar Lattes)</option><option value="48">SIC (Serviço de Informações ao Cidadão) / Praça da Paz</option><option value="25">Sobrapar</option></select>					</div>
	</td>
	<td valign="bottom">	
		<input type="submit" name="pesquisar" value="Enviar" class="botao" /> <br />
	</td>
	</tr>
	</table>

	</form>
	
	<div id="resultadoQualCircular" class="resultado_fretado">
			</div>

<script>
	$(document).ready(function(){
	   $("#frmBusca").on("submit", function(e){
	       e.preventDefault();
	
	        $.ajax({
	            url: "qualCircular_busca.php",
	            method: "POST",
	            dataType: "html",
	            data: $(this).serialize()
	        }).done(function(data){
	           $("#resultadoQualCircular").html(data);
	        }).fail(function(data){
	        });  
	   });
	});
</script>


		    </div>
	    </div>
	</div>

	<script>
	
		var modal = document.getElementById("myModal");
		var btn = document.getElementById("qualCircular");
		var span = document.getElementsByClassName("close")[0];
	
		btn.onclick = function() {
	
			modal.style.display = "block";
		    document.getElementById("resultadoQualCircular").innerHTML = '';  
	  
		}
	
		span.onclick = function() {
		    modal.style.display = "none";
		}
		
		window.onclick = function(event) {
		    if (event.target == modal) {
		        modal.style.display = "none";
		    }
		}
	
		modal.style.display = "none";

	</script>
</body>
</html>

