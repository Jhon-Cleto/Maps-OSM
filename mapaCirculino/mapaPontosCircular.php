<?php

require_once ("inc/config.php");

$hour = intval ( date ( 'H' ) );
$noturno = false;

if ($hour >= 18) {
	$noturno = true;
}

$conexao = pg_connect ( MAPS_CI_CONNECTION ) or die ( "Não foi possível conectar ao servidor" );

$htmlListTiposPontos = "";
$idxPonto = 0;
$tempTipoPonto = "";
$scriptJS = "";


$tipoLinha = $_POST ['tipoLinha'];
$tipoLinhaArray = explode ( ";", $tipoLinha );

// pegar circular (linha) e qual circulino
$circular = $tipoLinhaArray [0];
$circulino = $tipoLinhaArray [1];

// verificando a seleção de "Estou em"
if (! isset ( $_POST ['myLocal'] )) {
	$meuLocal = 0;
} else {
	$meuLocal = $_POST ['myLocal'];
}

$scriptJSHorarios = "";
$scriptJSPontoInicial = "var posicaoPontoInicial = new google.maps.LatLng(-22.828016, -47.060825);";
$scriptJSCentroUnicamp = "var posicaoCentroUnicamp = new google.maps.LatLng(-22.821677, -47.065283);";

// tentar resgatar Linha e Dispositivo (Circulino) quando não vierem setados
if ($circular == "" || $circulino == "") {
	
	$sqlPegarLinhaECirculino = "SELECT crl.id_linha_circulino, crl.id_circulino, cil.descricao 
								FROM circulino.circulino_linhas crl 
								INNER JOIN circular_linhas cil ON crl.id_linha_circulino = cil.id_circular_linha 
								ORDER BY cil.descricao, crl.ultima_atualizacao_circulino DESC, crl.onibus LIMIT 1";
	$rsPegarLinhaECirculino = pg_query ( $conexao, $sqlPegarLinhaECirculino );
	
	if (pg_num_rows ( $rsPegarLinhaECirculino ) > 0) {
		$row = pg_fetch_array ( $rsPegarLinhaECirculino );
		$circular = $row ['id_linha_circulino'];
		$circulino = $row ['id_circulino'];
	} else {
		$circular = 0;
		$circulino = 0;
	}
}

// sql para mostrar próxima saída ônibus
$sqlProximaSaidaOnibus = "SELECT TO_CHAR(horario, 'HH24:MI') as horario FROM site_mapa_pts_circular_interno
					WHERE id_circular_ponto = 1 AND id_circular_linha = " . $circular . " AND unidade <> 'FICTICIO' AND horario >= current_time AND horario <= current_time + interval '1 hour'
					LIMIT 1";

// executando a consulta
$resultProximaSaidaOnibus = pg_query ( $conexao, $sqlProximaSaidaOnibus );
if (! $resultProximaSaidaOnibus) {
	print pg_last_error ( $conexao );
	die ( "Erro durante consulta ao banco de dados." );
}

$proximoHorarioSaidaOnibus = "";
if (pg_num_rows ( $resultProximaSaidaOnibus ) > 0) {
	$row1 = pg_fetch_array ( $resultProximaSaidaOnibus );
	$proximoHorarioSaidaOnibus = $row1 ['horario'];
}

// sql para buscar os pontos
$sqlPontosCircular = "SELECT distinct id_circular_linha, id_circular_ponto, tem_cobertura, unidade, referencia, descricao, latitude, longitude, ordem
							 FROM site_mapa_pts_circular_interno 
							 WHERE latitude <> '' AND longitude <> '' AND id_circular_linha = $circular AND ordem > 0 
					  UNION 
					  SELECT distinct * FROM site_mapa_pts_ficticios_circular_interno
					         WHERE latitude <> '' AND longitude <> '' AND id_circular_linha = $circular AND ordem > 0  
					 				  		 
					  ORDER BY ordem";

// executando a consulta
$resultPontosCircular = pg_query ( $conexao, $sqlPontosCircular );
if (! $resultPontosCircular) {
	print pg_last_error ( $conexao );
	die ( "Erro durante consulta ao banco de dados." );
}

$idMarker = 0;

while ( $row1 = pg_fetch_array ( $resultPontosCircular ) ) {
	
	$id_rota = $row1 ['id_circular_linha'];
	
	// criando a variável que representará o balão
	$scriptJS .= "\nvar infowindow$idMarker = new google.maps.InfoWindow(); ";
	
	// consulta que retorna os horarios de acordo com o ponto escolhido
	$sql1 = "SELECT TO_CHAR(horario, 'HH24:MI') as horario, onibus_adaptado, referencia FROM site_mapa_pts_circular_interno
			WHERE id_circular_ponto = " . $row1 ['id_circular_ponto'] . " AND id_circular_linha = " . $circular . " AND horario >= current_time AND horario <= current_time + interval '1 hour'
			ORDER BY horario";
	
	// executando a consulta
	$result1 = pg_query ( $sql1 );
	
	if ($row1 ['unidade'] != 'FICTICIO') {
		
		$horario = "";
		$pontoProxHorario = "";
		
		while ( $rsPontoProximo = pg_fetch_array ( $result1 ) ) {
			
			if ($pontoProxHorario == "" && $rsPontoProximo ['horario'] > $proximoHorarioSaidaOnibus) {
				$pontoProxHorario = "'" . $rsPontoProximo ['horario'] . "'";
			}
			
			if ($horario == "") {
				$horario .= $rsPontoProximo ['horario'];
			} else {
				$horario .= ' - ' . $rsPontoProximo ['horario'];
			}
			
			$imgonibus = "";
			if ($rsPontoProximo ['onibus_adaptado'] == 't') {
				$imgonibus = "<img src=img/cadeirante.jpg style=\"width: 13px; height: 13px; margin-left: 3px;\">";
			}
			
			$horario .= $imgonibus;
		}
		
		if (pg_num_rows ( $result1 ) == 0) {
			$horario = "Este ponto não possui horários na próxima 1 hora.";
		}
		
		// buscando foto do ponto
		if (file_exists ( "img/fotosPontosCI/" . $row1 ['id_circular_ponto'] . ".JPG" )) {
			$imageFile = "img/fotosPontosCI/" . $row1 ['id_circular_ponto'] . ".JPG";
		} else {
			$imageFile = "img/fotosPontosCI/semImagem.png";
		}
		
		// tem cobertura?
		$cobertura = "";
		if ($row1 ['tem_cobertura'] == 't') {
			$cobertura = "Este ponto possui cobertura.";
		}
		
		// conteudo do balao de informação de cada ponto
		$content = "<table border=\"0\" width=\"350\">" . "<tr>" . "<td colspan=\"2\" align=\"center\"><b>" . $row1 ['unidade'] . " </b><br/></td>" . "</tr> " . "<tr>" . "<td  align=\"center\">" . $row1 ['referencia'] . "</td>" . "</tr> " . "</tr> " . "<tr>" . "<td  align=\"center\"><img src=\"$imageFile\" style=\"max-width: 80%; max-height: 80%;\"></td>" . "</tr> " . "<tr>" . "<td  align=\"center\"><b>Horários do " . $row1 ['descricao'] . "</b></td>" . "</tr> " . "<tr>" . "<td>" . $horario . "</td>" . "</tr> " . "<tr>" . "<td>" . $cobertura . "</td>" . "</tr> " . "<tr>" . "<td><img src=img/cadeirante.jpg style=\"width: 15px; height: 15px;\"> <font color=\"#0000FF\">Viagens com ônibus adaptado para deficientes físicos.</font></td>" . "</tr> " . "</table>";
		
		// preparando o balão de informação
		$scriptJS .= "infowindow$idMarker.setContent('$content');";
		
		// preparando as coordenadas
		$scriptJS .= "coordinates = new google.maps.LatLng(" . $row1 ['latitude'] . "," . $row1 ['longitude'] . ");\n";
		
		// selecionando a imagem para se exibida
		$image = "'img/marcadoresPontosCI/buildings.png'";
		
		$title = "'$row1[referencia]'";
		
		// adiciona o marcador no mapa
		$scriptJS .= "marker$idMarker = new google.maps.Marker({ position: coordinates, map: map, icon: $image, title: $title });\n";
		
		// adicionado listener (click) n marcador para o surgimento do balão
		$scriptJS .= "google.maps.event.addListener(marker$idMarker, 'click', function() {  clearInfoWindow(); infowindow$idMarker.open(map,marker$idMarker); });";
		

		$scriptJS .= "arrMarkers.push(marker$idMarker);";
		
		// adicionar janela de informação ao array
		$scriptJS .= "arrInfoWindows.push(infowindow$idMarker);";
		
		// Evento que fecha a infoWindow com click no mapa.
		$scriptJS .= "google.maps.event.addListener(map, 'click', function() { clearInfoWindow(); });";
		
		// Evento verifica.
		$scriptJS .= "google.maps.event.addListener(map, 'zoom_changed', function() { setVisibleMarkers(); });";
		
		// guardando as coordenadas do pontos para redimensionar o zoom do mapa depois
		// a declaração e o uso da função LatLngBounds estão na function Initialize()
		// $scriptJS .= "bounds.extend(coordinates)";
		
		$idMarker ++;
	}
	
	// criando o array para mostrar o próximo horário do onibus no ponto
	$scriptJSHorarios .= "arrPontoProxHorario.push($pontoProxHorario);";
	
	// adicionando os pontos de ônibus (inclusive os fictícios) em um array
	$scriptJS .= "var pontoOnibus = {ordem:" . $row1 ['ordem'] . ", unidade:'" . $row1 ['unidade'] . "', referencia:'" . $row1 ['referencia'] . "',  lat:" . $row1 ['latitude'] . ", lng: " . $row1 ['longitude'] . "};\n";
	$scriptJS .= "arrPontosOnibus.push(pontoOnibus);\n";
	
	// adicionar waypoints ao array
	$scriptJS .= "arrWaypts.push({location: new google.maps.LatLng(" . $row1 ['latitude'] . "," . $row1 ['longitude'] . "), stopover:true});\n";
}


// verificar se é necessário refazer o processo de reconstrução do filtro necessário pois  
// a consulta é pesada e não pode acontecer a cada clique do usuário em um item do filtro
if (!isset($_POST ['hidMontarFiltroTipoLinha'])) {
	$montarFiltroTipoLinha = true;
} else {
	$montarFiltroTipoLinha = $_POST ['hidMontarFiltroTipoLinha'];
}


$arrInputDescricao = array();			// array para guardar a descrição (texto) do filtro TipoLinha
$arrInputValues = array();				// array para guardar apenas values do filtro TipoLinha



// TRECHO PARA MONTAR O FILTRO "Tipo de Linha"
if ($montarFiltroTipoLinha) {
	
	$input = ""; // a variável $input conterá o HTML + values para montar o filtro TipoLinha (variável importante)
	
	
	// listar circulinos atuando no momento
	$sql1 = "SELECT ca.id_linha_circulino, ca.id_circulino, ca.descricao, ca.onibus, cc.id_empresa_instalada 
		 FROM circulino.view_circulinos_atuando ca
  	         INNER JOIN circulino.circulinos cc ON cc.id_circulino = ca.id_circulino";
	
	$result1 = pg_query ( $conexao, $sql1 );
	
	while ( $row3 = pg_fetch_array ( $result1 ) ) {
		
		$idCircularLinha = $row3 ['id_linha_circulino'];
		$idCirculino = $row3 ['id_circulino'];
		$idEmpresaInstalada = $row3 ['id_empresa_instalada'];	

		// verificar se o item está marcado (checked?)
		if ($circular == $idCircularLinha && $circulino == $idCirculino) {
			$checked = "checked";
		} else {
			$checked = "";
		}
		
		$onibus = "";
		if ($row3 ['onibus'] > 0) {
			$onibus = " - Ônibus " . $row3 ['onibus'];
		}
		
		$imgAdaptado = "";
		if ($row3 ['id_empresa_instalada'] == 20) { // MACTUR tem Ã´nibus adaptado
			$imgAdaptado = "<img src=img/cadeirante.jpg style=\"width: 13px; height: 13px; margin-left: 3px;\">";
		}	
		
		// neste ponto a variável $input pode receber as descrições fora de ordem (alfabética).
		$input .= "<input type=\"radio\" onchange=\"submitServico()\" id=\"tipoLinha\" name=\"tipoLinha\" value=\"" . $row3 ['id_linha_circulino'] . ";" . $row1 ['id_circulino'] . "\" $checked>" . $row1 ['descricao'] . $onibus . "<br>";
		
		array_push ( $arrInputDescricao, array (
				"descr" => $row3 ['descricao'] . $onibus . $imgAdaptado
		) );
		array_push ( $arrInputValues, array (
				"linha" => $row3 ['id_linha_circulino'],
				"circulino" => $row3 ['id_circulino'],
				"descr" => $row3 ['descricao'] . $onibus . $imgAdaptado
		) );
	
	}
	
	// solicita a construção do HTML
	construirHTMLFiltroTipoLinha ();
	
	$montarFiltroTipoLinha = false;
	
} else {

	//recuperar os valores existentes no hidden hidTempFiltroTipoLinha
	$strInput = $_POST["hidTempFiltroTipoLinha"];
	$arrInputValues = unserialize(base64_decode($strInput));
	
	// solicita a construção do HTML
	construirHTMLFiltroTipoLinha();
}

//função para montar a variável $input responsável por conter html e valores para o filtro
function construirHTMLFiltroTipoLinha(){
	$GLOBALS['input'] = "";

	foreach ($GLOBALS['arrInputValues'] as $in){

		// pegar a opção selecionada anteriormente
		if ($GLOBALS['circular'] == $in['linha']  && $GLOBALS['circulino'] == $in['circulino'] ) {
			$checked = "checked";
		} else {
			$checked = "";
		}
		
				$GLOBALS ['input'] .= "<input type=\"radio\" onchange=\"submitServico()\" id=\"tipoLinha\" name=\"tipoLinha\" value=\"" . $in ['linha'] . ";" . $in ['circulino'] . "\" " . $checked . ">". "<span style=\"font-weight: bold; color: ".getColor($in ['linha'])."\">" . $in ['descr'] . "</span><br>";
	}
}

// função para retornar a cor da linha
function getColor($linha){
	
	$cor = '';
	
	switch ($linha){
		case 1: $cor = "#0097C9"; break;	// azul
		case 2: $cor = "#7C7373"; break;	// cinza
		case 3: $cor = "#001B8E"; break;	// roxo
		case 4: $cor = "#005C0A"; break;	// verde
	}
	
	return $cor;
}

// função comparator para ser usada tipo callback para a função usort 
function compDescricao($a, $b) {
  if ($a["descr"] == $b["descr"]) {
    return 0;
  }
  return ($a["descr"] < $b["descr"]) ? -1 : 1;
}

?>

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
		
		var markerBus;
		var markerIAmHere;
		var arrMarkers = [];
		var arrInfoWindows = [];
		var arrWaypts = [];
		var arrPontoProxHorario = [];
		var arrPontosOnibus = [];

		var coordinates;
		var marker;

		var currentLatOnibus;
		var currentLgnOnibus;
		var currentLatUsuario = -22.817113;
		var currentLngUsuario = -47.069672;
		var currentVelocOnibus;
		var statusCoordinates;
		var lastSend = "";
		var lastAddress = "";

		var countCoordsIsNull = 0;

		var proximoHorarioSaidaOnibus = <?php print "\"$proximoHorarioSaidaOnibus\""?>;

		//var centralizarNoOnibus = document.getElementById("chkCentralizarNoOnibus");

		<?php print $scriptJSHorarios; ?>;
		<?php print $scriptJSPontoInicial; ?>
		<?php print $scriptJSCentroUnicamp; ?>		
		
		$(document).ready(function(){
			<?php if ($circular != 3 || ($circular == 3 && $noturno)) { ?>
				buscarPosicaoOnibusAjax();
			<?php } ?>

		});

		// recarrega a página a cada 3 segundos
		<?php if ($circular != 3 || ($circular == 3 && $noturno)) { ?>
			setInterval(function(){

				if (countCoordsIsNull >=10){

					location.reload(true);
					
				} else {
							
					buscarPosicaoOnibusAjax();
		
					if (statusCoordinates != 3) {
	
						showWhereIsBus();
					}
		
					if (traceRoute){
						route();
					}
				}
				
			}, 3000);
		<?php } ?>


		// função para buscar a posição do ônibus
		function buscarPosicaoOnibusAjax(){

			var dados = {
					idCircularLinha: <?php print $circular?>,
					idCirculino: <?php print $circulino?>
			};
					
			var idCircularLinha = <?php print $circular?>;
			var idCirculino = <?php print $circulino?>;

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
		
		// enviando o form
		function submitServico(){
			frm = window.document.form
			frm.action = 'mapaPontosCircular.php'
			frm.submit()
		}

		//função para corrigir arredondamento quando último dígito for 5
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
		
		//função para definir a localização
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

		// função para verificar se o ônibus está no ponto inicial
		function onibusEstaPontoInicial(){
			var posicaoOnibus = new google.maps.LatLng(currentLatOnibus, currentLgnOnibus);

			// considera-se ainda no ponto distância de 120 metros do ponto inicial
			return (distanceAtoB(posicaoOnibus, posicaoPontoInicial) <= 120);
		}

		// função para verificar se o usuário está no ponto inicial
		function usuarioEstaPontoInicial(){
			posicaoUsuario = new google.maps.LatLng(currentLatUsuario, currentLngUsuario);

			// considera-se estar no ponto distância de 120 metros do ponto inicial
			return (distanceAtoB(posicaoUsuario, posicaoPontoInicial) <= 120);
		}

		// função para atualizar conteúdo do div modalContentQualCircular (qual circular pegar?)
		function refreshDivModal(){
			$('#modalContentQualCircular').load('qualCircular.php?currentLatUsuario='+currentLatUsuario+'&currentLngUsuario='+currentLngUsuario);
		}
		
		// função para criar mapa, colocar marcadores e kml
		function initialize() {

			var options = {
					zoom: 15,
					mapTypeId: google.maps.MapTypeId.ROADMAP,	
					gestureHandling: 'greedy'
			};

			map = new google.maps.Map(document.getElementById("googleMaps"), options);

			// traça a rota do circular baseado no arquivo kml do mesmo
			var kmlLayer = new google.maps.KmlLayer({url: 'https://www.prefeitura.unicamp.br/apps/site/kml/circular/' + <?php echo $id_rota ?> + '.kml?rev=1', preserveViewport: true} );
			kmlLayer.setMap(map); // para mostrar a camada no mapa;

			map.setCenter(posicaoCentroUnicamp);
			
			putBusMarker();
			putIAmHereMarker();
		
			// lista os pontos de onibus
			<?php print $scriptJS?>

		}
						

		//função para colocar o marcador o ônibus
		function putBusMarker() { 
			
			//remove o marcador do mapa
			if (markerBus != null) {
				markerBus.setMap(null);
			}

				
			//cria o marcador e o adiona ao mapa	
			if (map != null) {
				markerBus = new google.maps.Marker({	map: map,
								  						zIndex: google.maps.Marker.MAX_ZINDEX + 1,
								  						position: new google.maps.LatLng(currentLatOnibus, currentLgnOnibus),
								  						draggable: false
							      			  	  });

					
			 if (statusCoordinates == 1){
				  markerBus.setIcon('img/marcadoresPontosCI/bus.png');
			  } else if (statusCoordinates == 2){
				  markerBus.setIcon('img/marcadoresPontosCI/busEstimatePos.png');
			  } else if (statusCoordinates == 3){
				  markerBus.setIcon('img/marcadoresPontosCI/busNoConnection.png');
			  }
			 
			}
			
			var centralizarNoOnibus = document.getElementById("chkCentralizarNoOnibus");
			
			// centralizar o mapa
			if (centralizarNoOnibus.checked){		
				if (!isNaN(markerBus.position.lat()) && !isNaN(markerBus.position.lng())){
					map.setCenter(markerBus.position);
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

		// funcao para limpar os baloes de informaçoes abertos
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

		//função para retornar a distância em metros entre A e B
		function distanceAtoB(pointA, pointB){
			
			latOrigem = pointA.lat(); 
			lngOrigem = pointA.lng();

			latDestino = pointB.lat();
			lngDestino = pointB.lng();

			
			distancia = 6371000*Math.acos(Math.cos(Math.PI*(90-latDestino)/180)*Math.cos((90-latOrigem)*Math.PI/180)+Math.sin((90-latDestino)*Math.PI/180)*Math.sin((90-latOrigem)*Math.PI/180)*Math.cos((lngOrigem-lngDestino)*Math.PI/180));

			return distancia;
		}
		
		//função para traçar a rota e exibir distância e tempo
		function route(){  

			traceRoute = true;
			
			currentLatOnibus = markerBus.position.lat();
		    currentLngOnibus = markerBus.position.lng();

			calcularDistanciaTempo = true;
			msgDistanciaTempo = "<span style=\"font-weight: bold\">";
			
			//verificando o ponto mais próximo do usuário e distância do usuario ao ponto
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

			// setando o ponto de ônibus mais próximo ao usuário
			pontoMaisProximoUsuario = new google.maps.LatLng(arrPontosOnibus[idxPontoMaisProximoUsuario].lat, arrPontosOnibus[idxPontoMaisProximoUsuario].lng);
			
			if (!onibusEstaPontoInicial() && !usuarioEstaPontoInicial()){

				//verificando o ponto mais próximo do ônibus e a sua distância do ônibus ao ponto.
				idxPontoMaisProximoOnibus=0;
				onibusPosicao = new google.maps.LatLng(markerBus.position.lat(), markerBus.position.lng()); 
				distanciaOnibusAPonto = 9999999999;

				for (i=0; i<arrPontosOnibus.length; i++){
					if (arrPontosOnibus[i].unidade != 'FICTICIO'){
						busStop = new google.maps.LatLng(arrPontosOnibus[i].lat, arrPontosOnibus[i].lng);
						distanceTmp = distanceAtoB(busStop, onibusPosicao);

						if (distanceTmp < distanciaOnibusAPonto) {
							if (i > idxPontoMaisProximoOnibus /*&&  (i - idxPontoMaisProximoOnibus) <= 10*/) { // para evitar pegar pontos fora da sequência
								idxPontoMaisProximoOnibus = i;
								distanciaOnibusAPonto = distanceTmp;
							}
						}
					}
				}

				// setando o ponto de ônibus mais próximo do ônibus
				pontoMaisProximoOnibus = new google.maps.LatLng(arrPontosOnibus[idxPontoMaisProximoOnibus].lat, arrPontosOnibus[idxPontoMaisProximoOnibus].lng);

				pontoIni = 0;
				pontoFim = 0;
				var arrPontosIntermediarios = [];
				
				if (idxPontoMaisProximoUsuario > idxPontoMaisProximoOnibus){ // ônibus não passou

					// reconstruir pontos intermediários 
					// (serão usados para forçar a passagem pelos pontos) 
					pontoIni = idxPontoMaisProximoOnibus + 1;
					pontoFim = idxPontoMaisProximoUsuario;
	
					for (i=pontoIni; i<=pontoFim; i++){
						arrPontosIntermediarios.push({location: new google.maps.LatLng(arrWaypts[i].location.lat(), arrWaypts[i].location.lng()), stopover:true});
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
			
			
			var msg = "";

            msg ='<span style="font-weight: bold">Atualmente o &#244;nibus est&#225; em ' + lastAddress;
            
            if (onibusEstaPontoInicial() && lastAddress.indexOf("Sabin") != -1){
            	msg += " (ponto inicial).";
            }

            msg += '</span>';
	        msg += '<br/><span style="font-weight: bold">A velocidade média atual é ' + currentVelocOnibus.toString() + ' km/h.</span>';

	        document.getElementById("endereco").innerHTML = msg;  

		}
	</script>
	

</head>
<body onload="setLocation();">
	<br />
	
	<table width="100%">
		<tbody>
			<tr>
				<td>	
					<p class="mapa_texto">
						<span style="color: #b45938; font-size: 18px; font-weight: bold;">
							Instruções gerais </span> <br /> <br /> - Selecione a linha desejada
						para carregar o itinerário (Circular I, Circular II via FEC, Circular
						II via Museu ou Circular Noturno) <br /> - Clique sobre o ponto
						desejado para obter os horários previstos de passagem dos ônibus nos
						próximos 60 minutos <br /> - Clique no botão traçar rota para ver a
						distância entre o ônibus e o ponto mais próximo de você 
						<br> - Para relação geral dos horários das linhas, acesse a página da 
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

			<form id="form" name="form" action="<?=$_SERVER['PHP_SELF']?>"
				method="POST">
				<input type="hidden" id="hidMontarFiltroTipoLinha" name="hidMontarFiltroTipoLinha"
					value="<?php echo $montarFiltroTipoLinha; ?>">
				<input type="hidden" id="hidTempFiltroTipoLinha" name="hidTempFiltroTipoLinha"
					value="<?php print base64_encode(serialize($arrInputValues)); ?>">										
				<div id="controls1"
					style="width: 280px; height: auto; float: left; margin-left: 25px;">
					<strong class="mapa_titulo_2">Estou em</strong><br /> <input
						type="radio" name="myLocal" id="myLocal" onclick="setLocation();"
						value="0" <?php if($meuLocal==0){echo "CHECKED";}?>>Marcador
					(arraste o marcador para indicar sua posição) <br /> <input
						type="radio" name="myLocal" id="myLocal" onclick="setLocation();"
						value="1" <?php if($meuLocal==1){echo "CHECKED";}?>>Minha
					localiza&#231;&#227;o (apenas para dispositivos com GPS) <br /> <br />
					<strong class="mapa_titulo_2">Tipo de Linha</strong><br /> 
					<?php echo $input; ?>
					<br /> <img src="img/cadeirante.jpg"
						style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens
						com &#244;nibus adaptado para deficientes f&#237;sicos</font> <br /> <br />
					<strong class="mapa_titulo_2">Opções</strong><br />
					<input type="checkbox" id="chkCentralizarNoOnibus">Centralizar no ônibus
					<br /><br /><br />
					<?php if ($circular != 3 || ($circular == 3 && $noturno)) { ?>
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
					<?php } ?>
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
		    	<?php  include_once("qualCircular.php"); ?>
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

<?php
	pg_close ( $conexao );
?>