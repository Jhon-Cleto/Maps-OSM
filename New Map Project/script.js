
var mymap = L.map('mymap', {
    center: [-22.822991, -47.064430],
    zoom: 15
});

var CartoDB_Positron = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19
});		

var CartoDB_Voyager = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
	attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
	subdomains: 'abcd',
	maxZoom: 19
}).addTo(mymap);

let marker = L.marker([-22.817011, -47.069764]).addTo(mymap)
    .bindPopup("<b>Hello World!</b><br/>I am a popup.").openPopup();

var popup = L.popup();

function onMapClick(e) {
    popup
        .setLatLng(e.latlng)
        .setContent("Você clicou no mapa em " + e.latlng.toString())
        .openOn(mymap);
}

mymap.on('click', onMapClick);

let mymark = L.marker([-22.821955, -47.069678]).addTo(mymap);

mymark.dragging.enable()

let busicon = L.icon({
    iconUrl: 'bus.png',
    iconSize: [25, 30]
});

mymark.setIcon(busicon);

let ImHere = L.icon({
    iconUrl: 'iamhere.png',
    iconSize: [45, 45]
});

marker.setIcon(ImHere);
marker.dragging.enable()

// Load kml file
fetch('1.kml')
    .then(res => res.text())
    .then(kmltext => {
        // Create new kml overlay
        const parser = new DOMParser();
        const kml = parser.parseFromString(kmltext, 'text/xml');
        const track = new L.KML(kml);
        mymap.addLayer(track);

        // Adjust map to show the kml
        const bounds = track.getBounds();
        mymap.fitBounds(bounds);
    });