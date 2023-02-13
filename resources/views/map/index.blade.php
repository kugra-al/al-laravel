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
                                @if(isset($facades) && sizeof($facades))
                                    <li><a href="#" onclick="toggleFacadeLayer();return false;">Toggle Facades ({{ $facades->count() }})</a></li>
                                @endif
                                <li><a href="#" onclick="toggleBuildingLayer();return false;">Toggle Buildings</a></li>
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
            @foreach($facades as $facade)
                {coords: [ {{ $facade->x }}, {{ $facade->y }} ], title: '{{ $facade->facade_id }}', destination: '{{ $facade->destination }}' },
            @endforeach
        ];
        var buildings = [
            {coords: [575, 1505], title: 'Some building'}
        ];
        var facadeIcon = new L.Icon({
            iconUrl: '/img/facade-icon.png',
            iconSize: [10, 10],
            iconAnchor: [1, 1],
            popupAnchor: [1, 1],
        });

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

//         var facadePopup = L.popup()
//             .setContent('<p>test</p>')
//             .openOn(map);

        for(i = 0; i < facades.length; i++) {
            var facade = facades[i];
            var marker = L.marker(xy(facade.coords[0], facade.coords[1]), {title: facade.title, icon: facadeIcon, data: {destination: facade.destination} });
            var domain;
            if (facade.destination.search('city_server/') != -1) {
                domain = facade.destination.split('city_server/')[0];
                domain = domain.replace('/domains/','');
            }
            marker.bindPopup(
                "<ul>"+
                "<li>ID: "+facade.title+"</li>"+
                "<li>Coords: "+facade.coords[0]+":"+facade.coords[1]+"</li>"+
                "<li>Destination: "+facade.destination+"</li>"+
                (domain.length ? "<li><a href='https://github.com/Amirani-al/Accursedlands-Domains/tree/master/"+domain+"' target='_blank'>View /domains/"+domain+" on Github</a></li>" : '')+
                "</ul>",
                {maxWidth: 'auto' }
            );
            marker.on('click',popupClick);
            facadeGroup.push(marker);

        }
        var facadeLayer = L.featureGroup(facadeGroup).addTo(map);
//         facadeLayer.bindPopup('Click');
//         facadeLayer.on('click',popupClick);
        function popupClick(e) {
            var popup = e.target.getPopup();
            console.log(e);
            var content = popup.getContent();

        }

        for(i = 0; i < buildings.length; i++) {
            var building = buildings[i];
            buildingGroup.push(L.marker(xy(building.coords[0], building.coords[1]), {title: building.title}));
        }
        var buildingLayer = L.layerGroup(buildingGroup).addTo(map);
        map.removeLayer(buildingLayer);
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
