<?php  

	header('Content-Type: text/html; charset=iso-8859-1');

//require_once ("../inc/config.php");

//$conexao = pg_connect ( MAPS_CI_CONNECTION ) or die ( "Não foi possível conectar ao servidor" );

?>
<!DOCTYPE html>
<html lang="pt-br">

    <head>
    
        <title> Circulino OSM </title>

        <meta charset="ISO-8859-1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		
        <link href='https://fonts.googleapis.com/css?family=PT+Sans+Narrow' rel='stylesheet' type='text/css'>

		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin=""/>
		<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
		
        <link rel="stylesheet" href="styles/leaflet.fullscreen.css">
        <script src="./scripts/Leaflet.fullscreen.min.js"></script>

        <link rel="stylesheet" href="styles/style.css">
        <link href="styles/mapa.css" rel="stylesheet" type="text/css" />
        <link href="styles/circular.css" rel="stylesheet" type="text/css" />
    
        <script type="text/javascript" src="./scripts/L.KML.js"></script>
        <script type="text/javascript" src="./scripts/map.js" charset="ISO-8859-1"></script>   
        
        <script type="text/javascript" src="./scripts/jquery.min.js"></script>   
        


    </head>

    <body onload="searchInput();">
        
        <br/>
	
        <table width="100%">
            <tbody>
                <tr>
                    <td>	
                        <p class="mapa_texto">
                            <span style="color: #b45938; font-size: 18px; font-weight: bold;">
                                Instru&#231;&#245;es gerais </span> <br/> <br/> - Selecione a linha desejada
                            para carregar o itiner&#225;rio (Circular I, Circular II via FEC, Circular
                            II via Museu ou Circular Noturno) <br/> - Clique sobre o ponto
                            desejado para obter os hor&#225;rios previstos de passagem dos &#244;nibus nos
                            pr&#243;ximos 60 minutos <br/> - Clique no bot&#227;o tra&#231;ar rota para ver a
                            dist&#226;ncia entre o &#244;nibus e o ponto mais pr&#243;ximos a voc&#234;
                            <br/> - Para rela&#231;&#227;o geral dos hor&#225;rios das linhas, acesse a p&#225;gina da 
                            <a href="https://www.prefeitura.unicamp.br/servicos/diretoria-de-servicos-de-transporte#circular">Unitransp</a>
                            <br/> 
			
                            <br/><br/>
                            <span font-size: 15px;>
                                Esta funcionalidade foi desenvolvida dentro do <a href="http://smartcampus.prefeitura.unicamp.br/" target="_blank">Projeto SmartCampus.</a>
                            </span>
                            
                            <br/>
                        </p>					
                    </td>
                    <td>	
                        <a href="http://smartcampus.prefeitura.unicamp.br/" target="_blank">
                            <img src="https://www.prefeitura.unicamp.br/imagens/estrutura/smart-campus-site.png"  alt="Projeto Smart Campus" title="Projeto Smart Campus" style="float:right;position:relative;margin-top: 0px; margin-right:350px;">
                        </a>
                            
                    </td>
                </tr>
            </tbody>
        </table>

        

        <div id="container" style="z-index:10;">
            <div id="mymap" style="z-index:10;"></div>

            <div style="float: left; margin-left: 0px; height: 600px; width: 30%; z-index:10;">
                <form id="form" name="form" action="<?=$_SERVER['PHP_SELF']?>" method="POST">

                    <div id="controls1" style="width: 280px; height: auto; float: left; margin-left: 25px;">


                        <strong class="mapa_titulo_2">Estou em</strong><br/>
                            
                            <input type="radio" name="myLocal" id="myLocal" onclick="setLocation();"
                                    value="0" checked>Marcador
                                        (arraste o marcador para indicar sua posi&#231;&#227;o) <br/>
                            
                            <input type="radio" name="myLocal" id="myLocal" onclick="setLocation();"
						            value="1" >Minha
                                        localiza&#231;&#227;o (apenas para dispositivos com GPS) <br/> <br/>

                        <strong class="mapa_titulo_2">Tipo de Linha</strong><br/>
                        
                        <div id="filtroLinha">

                        </div>

                        <br/>

                        <img src="img/cadeirante.jpg"style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com &#244;nibus adaptado para deficientes f&#237;sicos</font>
                        <br/><br/>

                        <div id="rotasMoradia" style="display: none;"> Rotas especiais Moradia: <br/>

						    <div id="legendaDiurno" style="display: inline">
                        			<img src="img/rotaAmarelo.png">&#193;rea da Sa&#250;de (#)<br/>
                            </div>
                            
                            <div id="legendaNoturno" style="display: none">
                                    <img src="img/rotaRoxo.png">Moradia via Terminal Bar&#227;o (B)<br/>
                                    <img src="img/rotaVerde.png">Moradia via Centro M&#233;dico (C)<br/>
                            </div>
                            <br/><br/><br/>
                            
					    </div>

                        <div id="divOptions">
    					    <strong class="mapa_titulo_2">Op&#231;&#245;es</strong><br />
    					    <input type="checkbox" id="chkCentralizarNoOnibus">Centralizar no &#244;nibus<br/><br/><br/>
					    </div>

                        <a id="traceRoute" style="padding-left:0em;">
                            <img src="img/btn_tracar_rota.png">
                            <br/><br/>
                        </a>
                        
                        <a id="qualCircular" style="padding-left:0em;">
							<img src="img/btn_qual_circular.png">
                            <br/>
						</a>
						
						<div id="details" class="mapa_texto"
							style="width: 280px; height: 65px; float: left; margin-left: 6px; margin-top: 10px;">
                        </div>
                        
                        <strong class="mapa_titulo_2">Legenda</strong><br/><br/>
						    <img src="img/bus.png"> - Posi&#231;&#227;o real<br/>
                            <img src="img/busEstimatePos.png"> - Posi&#231;&#227;o estimada devido perda moment&#226;nea de sinal<br/> 
                            <img src="img/busNoConnection.png"> - Perda prolongada de sinal<br/>
		                           
                    </div>           
                </form>
            </div>
        </div>

        <div id="resultado"></div>

        <div id="endereco" class="mapa_texto"
            style="float: left; margin-left: 10px; height: 50px; width: 630px;">
        </div>
        
        <!-- <div id="myModal" class="modal" style="width: 100%; display: none;">
            <div class="modal-content" style="width: 75%;">
                <span class="close">&times;</span>
                <script>refreshDivModal();</script>
                <div id="modalContentQualCircular">
                    <?php  //include_once("qualCircular.php"); ?>
                </div>
            </div>
	    </div>   -->
	    
	    <div id="myModal" class="modal" style="width: 100%; z-index:80;">
	    	<div class="modal-content" style="width: 75%; z-index:90;">
		    <span class="close">&times;</span>
		    <div id="modalContentQualCircular"  style="width: 75%; z-index:100;">
		    	<?php  include_once("../qualCircular.php"); ?>
		    </div>
		    <script>refreshDivModal();</script>
	    </div>
	</div>        
             
    </body>
</html>
