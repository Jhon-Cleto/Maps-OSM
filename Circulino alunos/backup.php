<?php


        
       


//       
//    //    ColocaPin([-22.816470, -47.072926]);

////DESCOMENTE AQUI PARA FORCAR O USO DO CACHE DO CLIENTE
//if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
//   header('HTTP/1.1 304 Not Modified');
//   die();
//}

//function caching_headers ($file, $timestamp) {
//    $lastModified=filemtime($_SERVER['SCRIPT_FILENAME']);
//    $gmt_mtime = gmdate("D, d M Y H:i:s T", $lastModified);
//    header('ETag: "'.md5($timestamp.$file).'"');
//    header('Last-Modified: '.$gmt_mtime);
//    header('Cache-Control: public, proxy-revalidate, max-age=3600');
//}
//
//caching_headers ($_SERVER['SCRIPT_FILENAME'], filemtime($_SERVER['SCRIPT_FILENAME']));

           
            


//    var grayscale = L.tileLayer( {id: 'mapid', attribution: mapboxAttribution}),
//    streets   = L.tileLayer( {id: 'mapid', attribution: mapboxAttribution});
//        
//    var baseMaps = {
//	"Grayscale": grayscale,
//	"Streets": streets
//    };    
    //    var overlayMaps = {
    //        "Rota 1": waypoints
    //    };
//    L.control.layers(baseMaps, overlayMaps).addTo(map);
    
//    
//        L.Routing.control({
//             waypoints: waypoints,
//             show: false,
//             draggableWaypoints : false
//        }).addTo(map);    
         
  
                 
//        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
//            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
//        }).addTo(map);
        
        
//        
//        $( document ).ready(function() {
//        });

//var num = 0;
//function ColocaPin(latlongi) {
//
//    var BusIcon = L.icon({
//        iconUrl: 'bus_station.png',
//        iconSize: [20, 30]
////                iconAnchor: [22, 94],
////                popupAnchor: [-3, -76],
////                shadowUrl: 'my-icon-shadow.png',
////                shadowSize: [68, 95],
////                shadowAnchor: [22, 94]
//    });         
//    L.marker(latlongi, {icon: BusStationIcon}).addTo(map);
//}


//                map.locate({setView: true, maxZoom: 16});
     
//              function onLocationFound(e) {
//                  var radius = e.accuracy;
//                  L.marker(e.latlng).addTo(map)
//                          .bindPopup("Voc&ecirc; est&aacute; aqui!").openPopup();
//
//                  L.circle(e.latlng, 130).addTo(map);
//              }
//
//              map.on('locationfound', onLocationFound);
//
//            //   function onLocationError(e) {
//            //           alert(e.message);
//            //   }
//
//              map.on('locationerror', onLocationError);