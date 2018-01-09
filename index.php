
<!DOCTYPE html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		  <title>WeMap!! Bot Map</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

      <!-- Leaflet 0.5: https://github.com/CloudMade/Leaflet-->
		<link rel="stylesheet" href="http://joker-x.github.io/Leaflet.geoCSV/lib/leaflet.css" />
		<!--[if lte IE 8]> <link rel="stylesheet" href="../../lib/leaflet.ie.css" />  <![endif]-->
		<script src="http://joker-x.github.io/Leaflet.geoCSV/lib/leaflet.js"></script>

		<!-- MarkerCluster https://github.com/danzel/Leaflet.markercluster -->
		<link rel="stylesheet" href="http://joker-x.github.io/Leaflet.geoCSV/lib/MarkerCluster.css" />
		<link rel="stylesheet" href="http://joker-x.github.io/Leaflet.geoCSV/lib/MarkerCluster.Default.css" />
		<!--[if lte IE 8]> <link rel="stylesheet" href="../../lib/MarkerCluster.Default.ie.css" /> <![endif]-->
		<script src="http://joker-x.github.io/Leaflet.geoCSV/lib/leaflet.markercluster-src.js"></script>

		<!-- GeoCSV: https://github.com/joker-x/Leaflet.geoCSV -->
		<script src="http://joker-x.github.io/Leaflet.geoCSV/leaflet.geocsv-src.js"></script>
 <script src='http://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-hash/v0.2.1/leaflet-hash.js'></script>
		<!-- jQuery 1.8.3: http://jquery.com/ -->
		<script src="http://joker-x.github.io/Leaflet.geoCSV/lib/jquery.js"></script>

		<style>
		html, body, #mapa {
			margin: 0;
			padding: 0;
			width: 100%;
			height: 100%;
			font-family: Arial, sans-serif;
			font-color: #38383;
		}

		#botonera {
			position:fixed;
			top:10px;
			left:50px;
			z-index: 2;
		}

		#cargando {
			position:fixed;
			top:0;
			left:0;
			width:100%;
			height:100%;
			background-color:#666;
			color:#fff;
			font-size:2em;
			padding:20% 40%;
			z-index:10;
		}

		.boton {
			border: 1px solid #96d1f8;
			background: #65a9d7;
			background: -webkit-gradient(linear, left top, left bottom, from(#3e779d), to(#65a9d7));
			background: -webkit-linear-gradient(top, #3e779d, #65a9d7);
			background: -moz-linear-gradient(top, #3e779d, #65a9d7);
			background: -ms-linear-gradient(top, #3e779d, #65a9d7);
			background: -o-linear-gradient(top, #3e779d, #65a9d7);
			padding: 12px 24px;
			-webkit-border-radius: 10px;
			-moz-border-radius: 10px;
			border-radius: 10px;
			-webkit-box-shadow: rgba(0,0,0,1) 0 1px 0;
			-moz-box-shadow: rgba(0,0,0,1) 0 1px 0;
			box-shadow: rgba(0,0,0,1) 0 1px 0;
			text-shadow: rgba(0,0,0,.4) 0 1px 0;
			color: white;
			font-size: 17px;
			/*font-family: Helvetica, Arial, Sans-Serif;*/
			text-decoration: none;
			vertical-align: middle;
		}
		.boton:hover {
			border-top-color: #28597a;
			background: #28597a;
			color: #ccc;
		}
		.boton:active {
			border-top-color: #1b435e;
			background: #1b435e;
		}
		#infodiv{
background-color: rgba(255, 255, 255, 0.6);

font-family: Helvetica, Arial, Sans-Serif;
padding: 2px;


font-size: 10px;
bottom: 13px;
left:5px;


max-height: 80px;

position: fixed;

overflow-y: auto;
overflow-x: hidden;
}
		</style>
	</head>
	<body>
		<div id="mapa"></div>
		<div id="infodiv" style="leaflet-popup-content-wrapper">
			<p><b>Mappatura partecipata<br></b>
			Oggetti mappati tramite <a href="https://telegram.me/wemapbot" targer="_blank">@wemapbot</a> su Telegram - by @piersoft</br>Dataset scaricabile in lic. CC0  in formato <a href="https://goo.gl/eER9ly">CSV</a></p>
		</div>
		<div id="cargando">Sto leggendo i dati...</div>

<script>

//;$(function() {

var mapa = L.map('mapa').setView([46.559, 9.141], 5);
var hash = L.hash(mapa);
L.tileLayer('http://tile.openstreetmap.org/{z}/{x}/{y}.png', {
	maxZoom: 20,attribution:'Map Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'}
).addTo(mapa);
var icona="icon.png";
var bankias = L.geoCsv(null, {firstLineTitles: true, fieldSeparator: ',',
	onEachFeature: function (feature, layer) {
		var popup = '';
		var str = ".jpg";
		var immagine ='';
		//var title = bankias.getPropertyTitle(clave);
	//	console.log(feature.properties.username+" lung "+feature.properties.username.length);
	if (feature.properties.username.length > 2){
			popup += 'Utente: <b>'+feature.properties.username+'</b><br />';
	}else {
		if (feature.properties.first_name.length > 2) popup += 'Nome: <b>'+feature.properties.first_name+'</b><br />';
	}
	if (feature.properties.text.length >0) popup += 'Messaggio: <b>'+feature.properties.text+'</b><br />';
		popup += 'del: <b>'+feature.properties.time+'</b><br />';
		popup += 'numero: <b>'+feature.properties.bot_request_message+'</b><br />';
		if (feature.properties.categoria.length >0) popup += 'Categoria: <b>'+feature.properties.categoria+'</b><br />';
	if (feature.properties.file_id.length > 0){

		console.log(feature.properties.file_id+" "+feature.properties.bot_request_message);
		immagine='http://www.piersoft.it/wemapbot/allegato.php?id='+feature.properties.file_id;

		popup += '<b><img src='+immagine+' style="width:250px;" alt="25"/></b><br /><b><a href="http://www.piersoft.it/wemapbot/allegato.php?id='+feature.properties.file_id+'" />Scarica allegato</a></b><br />';

	}
	if (feature.properties.aggiornata.length > 0){
		popup += 'Stato: <b>'+feature.properties.aggiornata+'</b><br />';
	}//else icona="icon.png";
	//	for (var clave in feature.properties) {
	//		var title = bankias.getPropertyTitle(clave);
	//		popup += '<b>'+title+'</b><br />'+feature.properties[clave]+'<br /><br />';
	//	}
		layer.bindPopup(popup);
	},
	pointToLayer: function (feature, latlng) {
		if (feature.properties.aggiornata.length > 0) icona="icon1.png";
		if (feature.properties.categoria =="Forze") icona="FORZE.png";
		if (feature.properties.categoria =="Debolezze") icona="DEBOLEZZE.png";
		if (feature.properties.categoria =="Pub") icona="PUB.png";
		if (feature.properties.categoria =="Dormire") icona="DORMIRE.png";
		if (feature.properties.categoria =="Civico") icona="CIVICO.png";
		if (feature.properties.categoria =="Albero") icona="ALBERO.png";
		if (feature.properties.categoria =="Ristorante") icona="RISTORANTE.png";
		if (feature.properties.categoria =="Palo luce") icona="LUCE.png";
		if (feature.properties.categoria =="Barriere") icona="BARRIERE.png";
if (feature.properties.categoria =="Energia e vita+") icona="piu.png";
if (feature.properties.categoria =="Energia e vita-") icona="meno.png";
if (feature.properties.categoria =="Clima e rischi+") icona="piu.png";
if (feature.properties.categoria =="Clima e rischi-") icona="meno.png";
if (feature.properties.categoria =="Ambiente e cultura+") icona="piu.png";
if (feature.properties.categoria =="Ambiente e cultura-") icona="meno.png";
if (feature.properties.categoria =="Società e inclusione+") icona="piu.png";
if (feature.properties.categoria =="Società e inclusione-") icona="meno.png";

	//	else icona="icon.png";
		return L.marker(latlng, {
			icon:L.icon({
				iconUrl: icona,
				shadowUrl: 'marker-shadow.png',
				iconSize: [32,37],
				shadowSize:   [41, 41],
				shadowAnchor: [16, 18]
			})
		});
	},
	firstLineTitles: true
});



$.ajax ({
	type:'GET',
	dataType:'text',
	cache: false,
	url:'db/map_data.txt',
   error: function() {
     alert('Non riesco a caricare i dati');
   },
	success: function(csv) {
      var cluster = new L.MarkerClusterGroup({disableClusteringAtZoom: 20});
		bankias.addData(csv);
		cluster.addLayer(bankias);
		mapa.addLayer(cluster);
	//	mapa.fitBounds(cluster.getBounds());
	},
   complete: function() {
      $('#cargando').delay(500).fadeOut('slow');
   }
});


$('#localizame').click(function(e) {
	mapa.locate();
	$('#localizame').text('Localizzando...');
	mapa.on('locationfound', function(e) {
		mapa.setView(e.latlng, 19);
		$('#localizame').text('trovato');
	});
});

//});
</script>

	</body>
</html>
