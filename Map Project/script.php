<?php 

require_once ("inc/config.php");

$hour = intval (date('H'));
$noturno = false;

if ($hour >= 18) {
	$noturno = true;
}

$conexao = pg_connect (MAPS_CI_CONNECTION) or die ("Não foi possível conectar ao servidor");


$scriptJS = "";


$tipoLinha = $_POST ['tipoLinha'];
$tipoLinhaArray = explode (";", $tipoLinha);

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

// tentar resgatar Linha e Dispositivo (Circulino) quando não vierem setados
if ($circular == "" || $circulino == "") {
	
	$sqlPegarLinhaECirculino = "SELECT crl.id_linha_circulino, crl.id_circulino, cil.descricao 
								FROM circulino.circulino_linhas crl 
								INNER JOIN circular_linhas cil ON crl.id_linha_circulino = cil.id_circular_linha 
								ORDER BY cil.descricao, crl.ultima_atualizacao_circulino DESC, crl.onibus LIMIT 1";
	$rsPegarLinhaECirculino = pg_query ($conexao, $sqlPegarLinhaECirculino);
	
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

// sql para buscar os pontos
$sqlPontosCircular = "SELECT distinct id_circular_linha, id_circular_ponto, tem_cobertura, unidade, referencia, descricao, latitude, longitude, ordem
							 FROM site_mapa_pts_circular_interno 
							 WHERE latitude <> '' AND longitude <> '' AND id_circular_linha = $circular AND ordem > 0 
					  UNION 
					  SELECT distinct * FROM site_mapa_pts_ficticios_circular_interno
					         WHERE latitude <> '' AND longitude <> '' AND id_circular_linha = $circular AND ordem > 0  
					 				  		 
					  ORDER BY ordem";

// executando a consulta
$resultPontosCircular = pg_query ($conexao, $sqlPontosCircular);
if (!$resultPontosCircular) {
	print pg_last_error ($conexao);
	die ("Erro durante consulta ao banco de dados.");
}

$idMarker = 0;

while ($row1 = pg_fetch_array($resultPontosCircular)){

	$id_rota = $row1 ['id_circular_linha'];
	
	// criando a variável que representará o balão
	$scriptJS .= "\nvar infowindow$idMarker = L.popup();";
	
	// consulta que retorna os horarios de acordo com o ponto escolhido
	$sql1 = "SELECT TO_CHAR(horario, 'HH24:MI') as horario, onibus_adaptado, referencia FROM site_mapa_pts_circular_interno
			WHERE id_circular_ponto = " . $row1 ['id_circular_ponto'] . " AND id_circular_linha = " . $circular . " AND horario >= current_time AND horario <= current_time + interval '1 hour'
			ORDER BY horario";
	
	// executando a consulta
	$result1 = pg_query($sql1);    

    if ($row1['unidade'] != 'FICTICIO') {
		
		$horario = "";
		$pontoProxHorario = "";
    
		while ($rsPontoProximo = pg_fetch_array($result1)){
			
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

        if (pg_num_rows ($result1) == 0) {
			$horario = "Este ponto não possui horários na próxima 1 hora.";
		}

        // buscando foto do ponto
		if (file_exists("img/fotosPontosCI/" . $row1['id_circular_ponto'] . ".JPG" )) {
			$imageFile = "img/fotosPontosCI/" . $row1['id_circular_ponto'] . ".JPG";
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
		$scriptJS .= "coordinates = L.latLng(" . $row1['latitude'] . "," . $row1 ['longitude'] . ");\n";    
        
        // Icone do ponto de ônibus
        $icon = "L.icon({iconUrl: 'img/marcadoresPontosCI/buildings.png', iconSize: [20, 32]})";
    
        $title = "'$row1[referencia]'";

        // adiciona o marcador no mapa
		$scriptJS .= "marker$idMarker = L.marker(coordinates, {icon: $icon, title: $title }).addTo(map);\n";

        $scriptJS .= "marker$idMarker.bindPopup(infowindow$idMarker);\n";

        // adicionando listener (click) n marcador para o surgimento do balão
        $scriptJS .= "marker$idMarker.addEventListener('click', function() { clearInfoWindow(); marker$idMarker.openPopup()});\n";

        $scriptJS .= "arrMarkers.push(marker$idMarker);";
		
		// adicionar janela de informação ao array
		$scriptJS .= "arrInfoWindows.push(infowindow$idMarker);";		
		
        $idMarker ++;
    }

	// criando o array para mostrar o próximo horário do ônibus no ponto
	$scriptJSHorarios .= "arrPontoProxHorario.push($pontoProxHorario);";
	
	// adicionando os pontos de ônibus (inclusive os fictícios) em um array
	$scriptJS .= "var pontoOnibus = {ordem:" . $row1 ['ordem'] . ", unidade:'" . $row1 ['unidade'] . "', referencia:'" . $row1 ['referencia'] . "',  lat:" . $row1 ['latitude'] . ", lng: " . $row1 ['longitude'] . "};\n";
    $scriptJS .= "arrPontosOnibus.push(pontoOnibus);\n";
    
	// adicionar waypoints ao array
	$scriptJS .= "arrWaypts.push({location: L.latLng(" . $row1 ['latitude'] . "," . $row1 ['longitude'] . "), stopover:true});\n";
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
	
	$result1 = pg_query ($conexao, $sql1);
	
	while ($row3 = pg_fetch_array($result1) ) {
		
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
			$onibus = " - ônibus " . $row3 ['onibus'];
		}
		
		$imgAdaptado = "";
		if ($row3 ['id_empresa_instalada'] == 20) { // MACTUR tem ônibus adaptado
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
		if ($GLOBALS['circular'] == $in['linha']  && $GLOBALS['circulino'] == $in['circulino']) {
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

<?php
	pg_close ($conexao);
?>