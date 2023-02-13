@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>

	<style>

		.leaflet-container {
			height: 600px;
			width: 1000px;
			max-width: 100%;
			max-height: 100%;
		}
		img.huechange { filter: hue-rotate(120deg); }
		img.huechange2 { filter: hue-rotate(60deg); }
		img.huechange3 { filter: hue-rotate(30deg); }
      #coords:before {content: "Coords: "}
	</style>

    <div class="container" style="max-width:90%">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __('Map') }}</div>

                    <div class="card-body">
                        <div id='map'></div>
                        <div id="coords"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>

        const map = L.map('map', {
            crs: L.CRS.Simple,
            minZoom: -3,
            maxZoom: 4
        });

        const yx = L.latLng;

        function xy(x, y) {
            if (Array.isArray(x)) { // When doing xy([x, y]);
                return yx(x[1], x[0]);
            }
            return yx(y, x); // When doing xy(x, y);
        }

        const bounds = [xy(0, 0), xy(2500, 2500)];
        // Random hash just so no curious player does /img/map.png
        const image = L.imageOverlay('img/map_bb0a99b14432697bd43cd80f0bd2cd77.png', bounds).addTo(map);

        map.setView(xy(545, 1493), 2);

        map.on("mousemove", function (event) {
            document.getElementById('coords').innerText = event.latlng.toString();
        });
        // y is off by 1 - need new map image
        var marker;
        marker = L.marker(xy(545, 1494),{title:'Forest Heart'}).addTo(map);
        marker._icon.classList.add("huechange");
        marker = L.marker(xy(570, 1470),{title:'Masokaska'}).addTo(map);
        marker._icon.classList.add("huechange2");
        marker = L.marker(xy(589, 1388),{title:'Banzar'}).addTo(map);
        marker._icon.classList.add("huechange3");
    </script>
@endsection
