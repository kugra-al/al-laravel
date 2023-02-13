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
      #coords:before {content: "Coords: "}
	</style>

    <div class="container" style="max-width:90%">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __('Map') }}</div>

                    <div class="card-body">
                        <div id="layerControls" style="float: right">
                            <h4>Layer Controls</h4>
                            <ul>
                                <li><a href="#" onclick="toggleFacadeLayer();return false;">Toggle Facade Layer</a></li>
                                <li><a href="#" onclick="toggleBuildingLayer();return false;">Toggle Building Layer</a></li>
                            </ul>
                        </div>
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

        var facadeGroup = [];
        var buildingGroup = [];
        // y is off by 1 - need new map image
        var facades = [
            {coords: [545, 1494], title: 'Forest Heart'},
            {coords: [570, 1470], title: 'Masokaska'},
            {coords: [589, 1388], title: 'Banzar'}
        ];
        var buildings = [
            {coords: [575, 1505], title: 'Some building'}
        ];
        var redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

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

        for(i = 0; i < facades.length; i++) {
            var facade = facades[i];
            facadeGroup.push(L.marker(xy(facade.coords[0], facade.coords[1]), {title: facade.title}));
        }
        var facadeLayer = L.layerGroup(facadeGroup).addTo(map);

        for(i = 0; i < buildings.length; i++) {
            var building = buildings[i];
            buildingGroup.push(L.marker(xy(building.coords[0], building.coords[1]), {title: building.title, icon: redIcon}));
        }
        var buildingLayer = L.layerGroup(buildingGroup).addTo(map);

        function toggleFacadeLayer() {
            if (map.hasLayer(facadeLayer))
                map.removeLayer(facadeLayer);
            else
                map.addLayer(facadeLayer);
        }
        function toggleBuildingLayer() {
            if (map.hasLayer(buildingLayer))
                map.removeLayer(buildingLayer);
            else
                map.addLayer(buildingLayer);
        }
    </script>
@endsection
