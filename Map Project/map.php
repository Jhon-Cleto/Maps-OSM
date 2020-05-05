

<!DOCTYPE html>
<html>
    <head>
        <title> Circulino OSM </title>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		

		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin=""/>
		<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
		
        <link rel="stylesheet" href="styles/style.css">
        <link href="styles/mapa.css" rel="stylesheet" type="text/css" />
        <link href="styles/circular.css" rel="stylesheet" type="text/css" />
        <link href='https://fonts.googleapis.com/css?family=PT+Sans+Narrow' rel='stylesheet' type='text/css'>
        
        <?php

          include('script2.php');

        ?>

        <script src="./scripts/L.KML.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="./scripts/map.js"></script>
        

    </head>

    <body onload="setLocation();">
        
        <br/>
	
        <table width="100%">
            <tbody>
                <tr>
                    <td>	
                        <p class="mapa_texto">
                            <span style="color: #b45938; font-size: 18px; font-weight: bold;">
                                Instruções gerais </span> <br/> <br/> - Selecione a linha desejada
                            para carregar o itinerário (Circular I, Circular II via FEC, Circular
                            II via Museu ou Circular Noturno) <br/> - Clique sobre o ponto
                            desejado para obter os horários previstos de passagem dos ônibus nos
                            próximos 60 minutos <br/> - Clique no botão traçar rota para ver a
                            distância entre o ônibus e o ponto mais próximo de você
                            <br/> - Para relação geral dos horários das linhas, acesse a página da 
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

        

        <div id="container">
            <div id="mymap"></div>

            <div style="float: left; margin-left: 0px; height: 600px; width: 30%;">
                <form id="form" name="form" action="<?=$_SERVER['PHP_SELF']?>" method="POST">

                    <div id="controls1" style="width: 280px; height: auto; float: left; margin-left: 25px;">


                        <strong class="mapa_titulo_2">Estou em</strong><br/>
                            
                            <input type="radio" name="myLocal" id="myLocal" onclick="setLocation();"
                                    value="0" <?php if($meuLocal==0){echo "CHECKED";}?>>Marcador
                                        (arraste o marcador para indicar sua posição) <br/>
                            
                            <input type="radio" name="myLocal" id="myLocal" onclick="setLocation();"
						            value="1" <?php if($meuLocal==1){echo "CHECKED";}?>>Minha
                                        localiza&#231;&#227;o (apenas para dispositivos com GPS) <br/> <br/>
                            
                        <strong class="mapa_titulo_2">Tipo de Linha</strong><br/>

                            <input type="radio" onchange="submitServico()" id="tipoLinha" name="tipoLinha" value="1;5" <?php echo $checked1 ?>>
                                <span style="font-weight: bold; color: #0097C9">Circular 1 (sentido anti-horário) - Ônibus 1<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></span>
                                <br/>

                            <input type="radio" onchange="submitServico()" id="tipoLinha" name="tipoLinha" value="2;6" <?php echo $checked2 ?>>
                                <span style="font-weight: bold; color: #7C7373">Circular 2 - via FEC (sentido horário)<img src=img/cadeirante.jpg style="width: 13px; height: 13px; margin-left: 3px;"></span>
                                <br/>

                            <input type="radio" onchange="submitServico()" id="tipoLinha" name="tipoLinha" value="5;0" <?php echo $checked3 ?>>
                                <span style="font-weight: bold; color: #1267FF">Ônibus Moradia</span>
                                <br/><br/>

                        <img src="img/cadeirante.jpg"style="width: 15px; height: 15px;"> <font color="#0000FF">Viagens com &#244;nibus adaptado para deficientes f&#237;sicos</font>
                        <br/><br/>

                        <div id="rotasMoradia" style="display: <?php echo  $display2?>"> Rotas especiais Moradia: <br/>

						    <div id="legendaDiurno" style="display: inline">
                        			<img src="img/rotaAmarelo.png">&#193;rea da Sa&#250;de (#)<br/>
                            </div>
                            
                            <div id="legendaNoturno" style="display: none">
                                    <img src="img/rotaRoxo.png">Moradia via Terminal Bar&#227;o (B)<br/>
                                    <img src="img/rotaVerde.png">Moradia via Centro M&#233;dico (C)<br/>
                            </div>
                            <br/><br/><br/>
                            
					    </div>

                        <div id="divOptions" style="display: <?php echo  $display1?>">
    					    <strong class="mapa_titulo_2">Opções</strong><br />
    					    <input type="checkbox" id="chkCentralizarNoOnibus">Centralizar no ônibus<br/><br/><br/>
					    </div>

                        <img src="img/btn_tracar_rota.png"><br/><br/>
                        
                        <a id="qualCircular" style="padding-left:0em;">
							<img src="img/btn_qual_circular.png">
						</a><br/>
						
						<div id="details" class="mapa_texto"
							style="width: 280px; height: 80px; float: left; margin-left: 10px; margin-top: 20px;">
                        </div>
                        
                        <strong class="mapa_titulo_2">Legenda</strong><br/><br/>
						    <img src="img/bus.png"> - Posição real<br/>
                            <img src="img/busEstimatePos.png"> - Posição estimada devido perda momentânea de sinal<br/> 
                            <img src="img/busNoConnection.png"> - Perda prolongada de sinal<br/>
		                           
                    </div>           
                </form>
            </div>
        </div>

        <div id="resultado"></div>

        <div id="endereco" class="mapa_texto"
            style="float: left; margin-left: 10px; height: 50px; width: 630px;">
        </div>   
             
    </body>
</html>