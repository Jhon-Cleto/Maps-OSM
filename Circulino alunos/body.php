<body style="margin: 0; padding: 0">
    <div align="center" style="position: absolute; width: 100%; margin-top: 1%; z-index: 9999; height: 30%" >
            <div align="center" style="border-radius: 6px; display: none; border-style: solid; border-color: black; border-width: 2px; background:  white; width: 30%; padding: 5">
                    <strong>MC855<br>
                    PROJETO CIRCULINO</strong>
            </div>
            <div align="center" style="border-radius: 6px; border-style: solid; border-color: black; border-width: 2px; background:  white; width: 320px;">
                Escolher Circular:<br>
                <select id="rotas" onchange="trocar_rota();">
                    <option value="1" <?php if ($_REQUEST['rota'] < 2) echo "selected";?>>Circular 1 (sentido anti-hor&aacute;rio)</option>
                    <option value="2" <?php if ($_REQUEST['rota'] == 2) echo "selected";?>>Circular 2 - via FEC (sentido hor&aacute;rio)</option>
                    <option value="3" <?php if ($_REQUEST['rota'] == 3) echo "selected";?>>Circular 2 - via Museu (sentido hor&aacute;rio)</option>
                    <option value="4" <?php if ($_REQUEST['rota'] == 4) echo "selected";?>>Circular Noturno (sentido hor&aacute;rio)</option>
                </select>
            </div>
        <Br> 
        <input type="button" value="teste_modal" id="teste_modal" data-toggle="modal" data-target="#alert" style="display: none; " >  
    </div>
    
    <div style="width: 100%; height: 100%;">
        <div id="map"></div>
    </div>
</body>
