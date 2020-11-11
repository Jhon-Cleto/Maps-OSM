<?php

	require_once("inc/config.php");
	
	if (!isset($conexao) || $conexao == null) {
		$conexao = pg_connect ( MAPS_CI_CONNECTION ) or die ( "N�o foi poss�vel conectar ao servidor" );
	}

	$cmd 		= isset($_POST['cmd']) ? trim($_POST['cmd']) : 0;
	$cid 		= isset($_POST['selCircular']) ? trim($_POST['selCircular']) : 0;
	$pidOrigem 	= isset($_POST['selPontoOrigem']) ? trim($_POST['selPontoOrigem']) : 0;
	$pidDestino = isset($_POST['selPontoDestino']) ? trim($_POST['selPontoDestino']) : 0;


	$htmlSelOrigem = '<select name="selPontoOrigem"	class="select_circular_ponto">';
	$htmlSelDestino = '<select name="selPontoDestino"	class="select_circular_ponto">';

 	$currentLatUsuario = isset($_REQUEST['currentLatUsuario']) ? trim($_REQUEST['currentLatUsuario']) : 0;
 	$currentLngUsuario = isset($_REQUEST['currentLngUsuario']) ? trim($_REQUEST['currentLngUsuario']) : 0;

	$sqlLinhaOrigem = "SELECT distinct circulino.calcular_distancia(latitude::double precision, longitude::double precision, $currentLatUsuario, $currentLngUsuario) dist, id_circular_ponto, referencia ";
	$sqlLinhaOrigem .="FROM public.site_mapa_pts_circular_interno ";
	$sqlLinhaOrigem .=  "ORDER BY 1 LIMIT 3";
	
	$sqlLinhaDestino = "select distinct id_circular_ponto, referencia  from site_mapa_pts_circular_interno ";
	$sqlLinhaDestino .=  "ORDER BY referencia";
	
	$rsLinhaOrigem = pg_query($conexao, $sqlLinhaOrigem)or die("Falha durante busca");
	$rsLinhaDestino = pg_query($conexao, $sqlLinhaDestino) or die("Falha durante busca");
	
	while($rowLinhaOrigem = pg_fetch_array($rsLinhaOrigem)){
		
		if($rowLinhaOrigem['id_circular_ponto'] == $pidOrigem){
			$selOrigem = ' selected ';
		}else{
			$selOrigem = '';
		}
		
		$htmlSelOrigem.='<option value="'.$rowLinhaOrigem['id_circular_ponto'].'"'.$selOrigem.'>'.$rowLinhaOrigem['referencia'].'</option>';
		$selOrigem = '';
	
	}
	
	while($rowLinhaDestino = pg_fetch_array($rsLinhaDestino)){
		
		if($rowLinhaDestino['id_circular_ponto'] == $pidDestino){
			$selDestino = ' selected ';
		}else{
			$selDestino = '';
		}
		
		$htmlSelDestino.='<option value="'.$rowLinhaDestino['id_circular_ponto'].'"'.$selDestino.'>'.$rowLinhaDestino['referencia'].'</option>';
		$selDestino = '';
	}
	
	$htmlSelOrigem.='</select>';
	$htmlSelDestino.='</select>';
?>


	<form method="post" name="frmBusca" id="frmBusca" ajax="true">
	
	<table width="80%" border="0">
	<tr>
	<td width="50%">
			<strong style="font-size: 14px;">Onde voc� est�?: (abaixo 3 pontos pr�ximos a voc�)</strong><br />
					<div id="pontoOrigem">
					<?php 
						echo $htmlSelOrigem;
					?>
					</div>
	</td>
		<td width="50%">
			<strong style="font-size: 14px;">Onde deseja ir?:</strong><br />
					<div id="pontoDestino">
					<?php 
						echo $htmlSelDestino;
					?>
					</div>
	</td>
	<td valign="bottom">	
		<input type="submit" name="pesquisar" value="Enviar" class="botao" /> <br />
	</td>
	</tr>
	</table>

	</form>
	
	<div id="resultadoQualCircular" class="resultado_fretado">
		<?php
			//trazer as linhas pesquisadas 
			if(isset($_POST['pesquisar']) || (!empty($cmd))){
				include_once("qualCircular_busca.php");
			}
	
		?>
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


