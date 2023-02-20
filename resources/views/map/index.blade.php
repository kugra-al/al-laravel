@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.1.0/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.1.0/dist/MarkerCluster.Default.css">
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.1.0/dist/leaflet.markercluster.js"></script>
	<style>

		.leaflet-container {
			height: 600px;
			width: 100%;
			max-width: 100%;
			max-height: 100%;
		}
		.marker-cluster div { width: auto; }
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
    @include('perms.modal')
    <script src="/js/L.Control.Layers.Tree.js"></script>
    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>

<link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
    <link rel="stylesheet" href="/css/L.Control.Layers.Tree.css">

    <script>

        const map = L.map('map', {
            crs: L.CRS.Simple,
            minZoom: -2,
            maxZoom: 7,
         //   drawControl: true
        });


   // Do whatever else you need to. (save to db; add to map etc)
//   map.addLayer(layer);

        const yx = L.latLng;

        var facadeGroup = [];
        var deathGroup = [];

        var facadeIcon = new L.Icon({
            iconUrl: '/img/facade-icon.png',
            iconSize: [10, 10],
            iconAnchor: [1, 1],
            popupAnchor: [1, 1],
        });

        var signpostIcon = new L.Icon({
            iconUrl: '/img/signpost-icon.svg',
            iconSize: [25, 25],
            iconAnchor: [1, 25],
            popupAnchor: [1, -25],
        });

        var purpleIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-violet.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        var redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        var alertIcon = redIcon;

        var tentIcon = new L.Icon({
            iconUrl: '/img/tent-icon.png',
            iconSize: [25, 25],
            iconAnchor: [1, 25],
            popupAnchor: [1, -25],
        });
        var buildingIcon = new L.Icon({
            iconUrl: '/img/building-icon.svg',
            iconSize: [25, 25],
            iconAnchor: [1, 25],
            popupAnchor: [1, -25],
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
            document.getElementById('coords').innerText = Math.round(event.latlng.lng)+":"+Math.round(event.latlng.lat);
        });
//         map.on("contextmenu", function (event) {
//             var newMarker = new L.marker(event.latlng).addTo(map);
//         });

        // y is off by 1 - need new map image
        var overlays = {
            facades:
                [
                    @foreach($facades as $facade)
                        {coords: [ {{ $facade->x }}, {{ $facade->y }} ], title: '{{ $facade->facade_id }}', destination: '{{ $facade->destination }}' },
                    @endforeach
                ],
            @if(isset($deaths) && sizeof($deaths))
            'deaths':
                [
                    @foreach($deaths as $death)
                        {coords: [ {{ $death->x }}, {{ $death->y }} ], title: '{{ $death->event_date }}', destination: '{{ $death->location }}', data: {'event_date':'{{ $death->event_date }}', 'player':'{{ $death->player }}','cause':'{{ $death->cause }}','location':'{{ $death->location }}' } },
                    @endforeach
                ],
            'perms' :
                [
                    @foreach($perms as $perm)
                        {coords: [ {{ $perm->x }}, {{ $perm->y }} ], title: '{{ $perm->filename }}', data: {
                            'short': '{{ $perm->short }}',
                            'id': '{{ $perm->id }}',
                            'object': '{{ $perm->object }}',
                            'location': '{{ $perm->location }}',
                            'filename' : '{{ $perm->filename }}',
                            'lastseen': '{{ $perm->lastseen }}',
                            'sign_title': '{{ $perm->sign_title }}',
                            'last_touched': '{{ $perm->last_touched }}',
                            'touched_by': '{{ $perm->touched_by }}',
                            'psets': '{{ $perm->psets }}',
                            'type': '{{ $perm->perm_type }}',
                            'destroyed' : {!! json_encode($perm->destroyed) !!}
                        }},
                    @endforeach
                ]
            @endif
        };

        for(i = 0; i < overlays.facades.length; i++) {
            var facade = overlays.facades[i];
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
            facadeGroup.push(marker);
        }
        var deathLayer = L.markerClusterGroup();
        for(i = 0; i < overlays.deaths.length; i++) {
            var death = overlays.deaths[i];
            var marker = L.marker(xy(death.coords[0], death.coords[1], {title: death.title}));
            var popupContents = "";
            Object.keys(death.data).forEach(function(key, value) {
                popupContents += "<li>"+key+": "+death.data[key]+"</li>";
            });
            marker.bindPopup(
                "<ul>"+
                popupContents+
                "</ul>"
            );
            deathLayer.addLayer(marker);
        }

        var buildingLayer = L.markerClusterGroup();
        var signpostLayer = L.markerClusterGroup();
        var destroyedLayer = L.markerClusterGroup();
        var unfinishedLayer = L.markerClusterGroup();
        var otherPermLayer = L.markerClusterGroup();
        var tentLayer = L.markerClusterGroup();
        var edraLayer = L.markerClusterGroup();
        var moorvaLayer = L.markerClusterGroup();
        for(i = 0; i < overlays.perms.length; i++) {
            var perm = overlays.perms[i];
            var options = {title: perm.title, icon: buildingIcon};
            switch(perm.data.object) {
                case "/obj/base/misc/signpost" :
                    options.icon = signpostIcon;
                    break;
                case "/obj/base/containers/permanent_well":
                case "/std/shop_shelves":
                case "/obj/base/vehicles/rowboat":
                    options.icon = purpleIcon;
                    break;
                case "/obj/items/other/conical_leather_tent":
                case "/obj/items/other/large_canvas_tent":
                    options.icon = tentIcon;
                    break;
                default :
                    if (perm.data.object.search('/wiz/') != -1 || !perm.data.object.length)
                        options.icon = alertIcon;

                    delete perm.data.sign_title;
                    break;
            }
            var marker = L.marker(xy(perm.coords[0], perm.coords[1]), options);

            var popupContents = "";
            Object.keys(perm.data).forEach(function(key, value) {
                popupContents += "<li>"+key+": "+perm.data[key]+"</li>";
            });
            marker.bindPopup(
                "<ul>"+
                popupContents+
                "</ul><a href='#' onclick='loadPermData("+perm.data.id+");'>View Data</a>"
            );
            if (perm.data.object == "/obj/base/misc/signpost")
                signpostLayer.addLayer(marker);
            else {
                if (perm.data.destroyed)
                    destroyedLayer.addLayer(marker);
                else {
                    if (perm.data.object == "/obj/base/misc/unfinished_perm")
                        unfinishedLayer.addLayer(marker);
                    else {
                        if (perm.data.type == "building")
                            buildingLayer.addLayer(marker);
                        else {
                            if(perm.data.type == "tent")
                                tentLayer.addLayer(marker);
                            else
                                otherPermLayer.addLayer(marker);
                        }
                    }
                }
            }
        }

        L.polygon([xy(590, 1379), xy(622, 1438), xy(607,1504), xy(577, 1529), xy(554, 1546), xy(537, 1494), xy(553, 1385)],
            {color: 'red', weight: 1}).on('click', function(e) {
            // There event is event object
            // there e.type === 'click'
            // there e.lanlng === L.LatLng on map
            // there e.target.getLatLngs() - your rectangle coordinates
            // but e.target !== rect
            console.info(e);
        }).addTo(edraLayer);

        L.polygon([xy(609,1383), xy(585,1365), xy(582, 1321), xy(558, 1317), xy(564, 1292),
            xy(616, 1300), xy(653, 1369), xy(653, 1407), xy(622,1412)],{color:'blue', weight: 1}).addTo(moorvaLayer);

    //    L.rectangle([xy(536, 1382), xy(597, 1550)], {color: 'blue', weight: 1}).on('click', function (e) {
            // There event is event object
            // there e.type === 'click'
            // there e.lanlng === L.LatLng on map
            // there e.target.getLatLngs() - your rectangle coordinates
            // but e.target !== rect
         //   console.info(e);
     //   }).addTo(edraLayer);

//         var drawnItems = new L.FeatureGroup();
//         map.addLayer(drawnItems);
//         var drawControl = new L.Control.Draw({
//             edit: {
//                 featureGroup: drawnItems
//             }
//         });
//         map.addControl(drawControl);


        var facadeLayer = L.featureGroup(facadeGroup, 'Facades').addTo(map);
        var layerControl = L.control.layers.tree(null,
            {
                'label': 'Overlays',
                'children':
                [
                    {
                        'label': 'Areas', selectAllCheckbox: true,
                        'children': [
                            {'label':'Facades', 'layer': facadeLayer },
                            {
                                'label':'Regions', selectAllCheckbox: true,
                                'children': [
                                    {'label':'Kingdom of Edra','layer': edraLayer },
                                    {'label':'Moorva','layer': moorvaLayer }
                                ]
                            }
                        ]
                    },
                    {
                        'label': 'Events', selectAllCheckbox: true,
                        'children': [
                            {'label':'Deaths', 'layer': deathLayer }
                        ]
                    },
                    {
                        'label':'Perms', selectAllCheckbox: true,
                        'children': [
                            {'label':'Buildings', 'layer': buildingLayer },
                            {'label':'Tents', 'layer': tentLayer },
                            {'label':'Destroyed Buildings/tents', 'layer': destroyedLayer },
                            {'label':'Unfinished Buildings', 'layer': unfinishedLayer },
                            {'label':'Signposts', 'layer': signpostLayer },
                            {'label':'Other Perms', 'layer': otherPermLayer },
                        ]
                    },
//                     {
//                         'label':'Custom', selectAllCheckbox: true,
//                         'children': [
//                             {'label':'Drawn', 'layer':drawnItems}
//                         ]
//                     }
                ]
            },

//             {
//                 'label':'Draw layer',
//                 'children' : [
//                     {'label':'Draw Layer', layer:drawnItems}
//                 ]
//
//             }
//             "Facades " : facadeLayer,
//             "Deaths ": deathLayer,
//             'Buildings' : buildingLayer,
//             'Tents': tentLayer,
//             'Destroyed Buildings/Tents' : destroyedLayer,
//             'Unfinished Buildings' : unfinishedLayer,
//             'Signposts' : signpostLayer,
//             'Other Perms': otherPermLayer
        ).addTo(map);
  // FeatureGroup is to store editable layers
        map.pm.addControls({
            drawControls: true,
            editControls: true,
            optionsControls: true,
            customControls: true,
            oneBlock: false,
        });
map.pm.setGlobalOptions({continueDrawing:false});
        map.on('pm:drawend', function(e) {
            console.log(e);
          //  e.layer.options.color = '#000';
   //         e.layer.addTo(drawnItems);
    //        drawnItems.add(e);
        });
    </script>
@endsection
