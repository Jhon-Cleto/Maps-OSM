<?
require_once("inc/config.php");

$pidOrigem 		= isset($_REQUEST['selPontoOrigem']) ? trim($_REQUEST['selPontoOrigem']) : 0;
$pidDestino 	= isset($_REQUEST['selPontoDestino']) ? trim($_REQUEST['selPontoDestino']) : 0;

if (!isset($conexao) || $conexao == null) {
	$conexao = pg_connect ( MAPS_CI_CONNECTION ) or die ( "N�o foi poss�vel conectar ao servidor" );
}

$sql = "SELECT (SELECT DISTINCT referencia FROM site_mapa_pts_circular_interno s1
				WHERE id_circular_ponto = $pidOrigem) origem,
       		   (SELECT DISTINCT referencia FROM site_mapa_pts_circular_interno s2
				WHERE id_circular_ponto = $pidDestino 
			   ) destino";

$rs = pg_query($conexao, $sql) or die("Falha durante busca");
$rsOrigemDestino = pg_fetch_array($rs);

$pontoOrigem = $rsOrigemDestino['origem'];
$pontoDestino = $rsOrigemDestino['destino'];

$sql = "select distinct descricao, id_circular_linha
		from site_mapa_pts_circular_interno s1
		where exists (select distinct id_circular_linha from site_mapa_pts_circular_interno s2
					   where id_circular_ponto = $pidOrigem and id_circular_linha = s1.id_circular_linha)
		and exists (select distinct id_circular_linha from site_mapa_pts_circular_interno s3
					   where id_circular_ponto = $pidDestino and id_circular_linha = s1.id_circular_linha)
		order by descricao ";

$rs1 = pg_query($conexao, $sql) or die("Falha durante busca");

$html = '';

while($rowLinhas = pg_fetch_array($rs1)){

	$sql = "select (select distinct ordem from site_mapa_pts_circular_interno s4
			   where id_circular_ponto = $pidOrigem and id_circular_linha = " . $rowLinhas['id_circular_linha'].
			"   order by ordem limit 1) <
			   (select distinct ordem from site_mapa_pts_circular_interno s5
			   where id_circular_ponto = $pidDestino and id_circular_linha = ".$rowLinhas['id_circular_linha'] .
			"   order by ordem limit 1) valido";
	
	$rs2 = pg_query($conexao, $sql) or die("Falha durante busca");
	$rsValido = pg_fetch_array($rs2);
	
	$linha = $rowLinhas['descricao'];

	$valido = "Esta linha vai at� o destino";

	if ($rsValido['valido'] == 't'){
		
		$sqlHorarioOrigem = "select distinct referencia, unidade, TO_CHAR(horario, 'HH24:MI') 
				as horario, onibus_adaptado from site_mapa_pts_circular_interno 
				where latitude <> '' AND longitude <> '' and id_circular_ponto = $pidOrigem 
				and (horario :: time) >= CURRENT_TIME and (horario :: time) <= CURRENT_TIME + interval ' 1 hour'
				and id_circular_linha = " . $rowLinhas['id_circular_linha'].
				"ORDER BY horario ";
		
		$rs3 = pg_query($conexao, $sqlHorarioOrigem) or die("Falha durante busca");
		
		$htmlHorarioOrigem = '';
		$htmlHorarioDestino = '';
		$destino='';
		
		while($rowHorariosOrigem = pg_fetch_array($rs3)){
	
			$destino='';
			
			if ((strlen($htmlHorarioOrigem)) != 0){
				$htmlHorarioOrigem.= ", ";
			}
			
			$htmlHorarioOrigem.= $rowHorariosOrigem['horario'] ;
			$adaptado = ($rowHorariosOrigem['onibus_adaptado']=='f')?'false':'true';
			
			$sqlHorarioDestino = "select distinct referencia, unidade, TO_CHAR(horario, 'HH24:MI')
			as horario, onibus_adaptado from site_mapa_pts_circular_interno
			where latitude <> '' AND longitude <> '' and id_circular_ponto = $pidDestino and 
			onibus_adaptado = $adaptado
			and id_circular_linha = " . $rowLinhas['id_circular_linha']. " and (horario :: time) > '".$rowHorariosOrigem['horario']. "' ".
			"ORDER BY horario LIMIT 1";
			
			$rs4 = pg_query($conexao, $sqlHorarioDestino) or die("Falha durante busca");
			$rowHorariosDestino= pg_fetch_array($rs4);
			
			if ((strlen($htmlHorarioDestino)) != 0){
				$htmlHorarioDestino.= ", ";
			}
				
			$htmlHorarioDestino.= $rowHorariosDestino['horario'];
		}
		
		if ((strlen($htmlHorarioOrigem)) == 0){
			$htmlHorarioOrigem = "Sem previs�o para pr�xima 1 hora";
		}
		
		if ((strlen($htmlHorarioDestino)) == 0){
			$htmlHorarioDestino = "Sem previs�o para pr�xima 1 hora";
		}		
		
		$html.= "<tr>";
		$html.= "<td align=\"left\"> <span style=\"font-size: 14px;\">$linha<br/></td>";
		$html.= "<td align=\"left\"> <span style=\"font-size: 14px;\">$htmlHorarioOrigem</td>";
		$html.= "<td align=\"left\"> <span style=\"font-size: 14px;\">$htmlHorarioDestino</td>";
		
		$html.= "</tr>";
	} 
}

if (strlen($html) == 0){
	$html.= "<tr>";
	$html.= "<td align=\"left\" <span style=\"font-size: 14px;\">Nenhuma linha encontrada</td>";
	$html.= "<td align=\"center\" <span class=\"space\"></td>";
	$html.= "<td align=\"center\" <span class=\"space\"></td>";
	$html.= "</tr>";
}

?>

	 <p class="titulo">Resultado da Busca</p>
	 
	 <table width="750" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="30%" height="50" align="left"><strong style="font-size: 14px;">Linha</strong></td>
			<td width="35%" align="center"><strong style="font-size: 14px;"><?php echo $pontoOrigem; ?></strong></td>
			<td width="35%" align="center"><strong style="font-size: 14px;"><?php echo $pontoDestino; ?></strong></td>
		</tr>
	
		<?php echo $html;?>

	</table>
