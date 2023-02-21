@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.1.0/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.1.0/dist/MarkerCluster.Default.css">
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.1.0/dist/leaflet.markercluster.js"></script>


    <div class="container" style="max-width:90%">
        <div class="row justify-content-center">
            <div class="col-md-12">

                        <div id='map'></div>

                        <div id="coords"></div>

            </div>
        </div>
    </div>
    @include('perms.modal')
    <script src="/js/L.Control.Layers.Tree.js"></script>
    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
    <link rel="stylesheet" href="/css/L.Control.Layers.Tree.css">
	<style>

		.leaflet-container {
			height: 600px;
			width: 100%;
			max-width: 100%;
			max-height: 100%;
		}
		.marker-cluster div { width: auto; }
		.leaflet-pm-toolbar .leaflet-pm-icon-trash {
            background-image: url(data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9IjEwMDAiIHdpZHRoPSI4NzUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTAgMjgxLjI5NmwwIC02OC4zNTVxMS45NTMgLTM3LjEwNyAyOS4yOTUgLTYyLjQ5NnQ2NC40NDkgLTI1LjM4OWw5My43NDQgMGwwIC0zMS4yNDhxMCAtMzkuMDYgMjcuMzQyIC02Ni40MDJ0NjYuNDAyIC0yNy4zNDJsMzEyLjQ4IDBxMzkuMDYgMCA2Ni40MDIgMjcuMzQydDI3LjM0MiA2Ni40MDJsMCAzMS4yNDhsOTMuNzQ0IDBxMzcuMTA3IDAgNjQuNDQ5IDI1LjM4OXQyOS4yOTUgNjIuNDk2bDAgNjguMzU1cTAgMjUuMzg5IC0xOC41NTMgNDMuOTQzdC00My45NDMgMTguNTUzbDAgNTMxLjIxNnEwIDUyLjczMSAtMzYuMTMgODguODYydC04OC44NjIgMzYuMTNsLTQ5OS45NjggMHEtNTIuNzMxIDAgLTg4Ljg2MiAtMzYuMTN0LTM2LjEzIC04OC44NjJsMCAtNTMxLjIxNnEtMjUuMzg5IDAgLTQzLjk0MyAtMTguNTUzdC0xOC41NTMgLTQzLjk0M3ptNjIuNDk2IDBsNzQ5Ljk1MiAwbDAgLTYyLjQ5NnEwIC0xMy42NzEgLTguNzg5IC0yMi40NnQtMjIuNDYgLTguNzg5bC02ODcuNDU2IDBxLTEzLjY3MSAwIC0yMi40NiA4Ljc4OXQtOC43ODkgMjIuNDZsMCA2Mi40OTZ6bTYyLjQ5NiA1OTMuNzEycTAgMjUuMzg5IDE4LjU1MyA0My45NDN0NDMuOTQzIDE4LjU1M2w0OTkuOTY4IDBxMjUuMzg5IDAgNDMuOTQzIC0xOC41NTN0MTguNTUzIC00My45NDNsMCAtNTMxLjIxNmwtNjI0Ljk2IDBsMCA1MzEuMjE2em02Mi40OTYgLTMxLjI0OGwwIC00MDYuMjI0cTAgLTEzLjY3MSA4Ljc4OSAtMjIuNDZ0MjIuNDYgLTguNzg5bDYyLjQ5NiAwcTEzLjY3MSAwIDIyLjQ2IDguNzg5dDguNzg5IDIyLjQ2bDAgNDA2LjIyNHEwIDEzLjY3MSAtOC43ODkgMjIuNDZ0LTIyLjQ2IDguNzg5bC02Mi40OTYgMHEtMTMuNjcxIDAgLTIyLjQ2IC04Ljc4OXQtOC43ODkgLTIyLjQ2em0zMS4yNDggMGw2Mi40OTYgMGwwIC00MDYuMjI0bC02Mi40OTYgMGwwIDQwNi4yMjR6bTMxLjI0OCAtNzE4LjcwNGwzNzQuOTc2IDBsMCAtMzEuMjQ4cTAgLTEzLjY3MSAtOC43ODkgLTIyLjQ2dC0yMi40NiAtOC43ODlsLTMxMi40OCAwcS0xMy42NzEgMCAtMjIuNDYgOC43ODl0LTguNzg5IDIyLjQ2bDAgMzEuMjQ4em0xMjQuOTkyIDcxOC43MDRsMCAtNDA2LjIyNHEwIC0xMy42NzEgOC43ODkgLTIyLjQ2dDIyLjQ2IC04Ljc4OWw2Mi40OTYgMHExMy42NzEgMCAyMi40NiA4Ljc4OXQ4Ljc4OSAyMi40NmwwIDQwNi4yMjRxMCAxMy42NzEgLTguNzg5IDIyLjQ2dC0yMi40NiA4Ljc4OWwtNjIuNDk2IDBxLTEzLjY3MSAwIC0yMi40NiAtOC43ODl0LTguNzg5IC0yMi40NnptMzEuMjQ4IDBsNjIuNDk2IDBsMCAtNDA2LjIyNGwtNjIuNDk2IDBsMCA0MDYuMjI0em0xNTYuMjQgMGwwIC00MDYuMjI0cTAgLTEzLjY3MSA4Ljc4OSAtMjIuNDZ0MjIuNDYgLTguNzg5bDYyLjQ5NiAwcTEzLjY3MSAwIDIyLjQ2IDguNzg5dDguNzg5IDIyLjQ2bDAgNDA2LjIyNHEwIDEzLjY3MSAtOC43ODkgMjIuNDZ0LTIyLjQ2IDguNzg5bC02Mi40OTYgMHEtMTMuNjcxIDAgLTIyLjQ2IC04Ljc4OXQtOC43ODkgLTIyLjQ2em0zMS4yNDggMGw2Mi40OTYgMGwwIC00MDYuMjI0bC02Mi40OTYgMGwwIDQwNi4yMjR6Ii8+PC9zdmc+);
        }
        .leaflet-pm-toolbar .leaflet-pm-icon-save {
            background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaGVpZ2h0PSIxNzkyIiB2aWV3Qm94PSIwIDAgMTc5MiAxNzkyIiB3aWR0aD0iMTc5MiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNNTEyIDE1MzZoNzY4di0zODRoLTc2OHYzODR6bTg5NiAwaDEyOHYtODk2cTAtMTQtMTAtMzguNXQtMjAtMzQuNWwtMjgxLTI4MXEtMTAtMTAtMzQtMjB0LTM5LTEwdjQxNnEwIDQwLTI4IDY4dC02OCAyOGgtNTc2cS00MCAwLTY4LTI4dC0yOC02OHYtNDE2aC0xMjh2MTI4MGgxMjh2LTQxNnEwLTQwIDI4LTY4dDY4LTI4aDgzMnE0MCAwIDY4IDI4dDI4IDY4djQxNnptLTM4NC05Mjh2LTMyMHEwLTEzLTkuNS0yMi41dC0yMi41LTkuNWgtMTkycS0xMyAwLTIyLjUgOS41dC05LjUgMjIuNXYzMjBxMCAxMyA5LjUgMjIuNXQyMi41IDkuNWgxOTJxMTMgMCAyMi41LTkuNXQ5LjUtMjIuNXptNjQwIDMydjkyOHEwIDQwLTI4IDY4dC02OCAyOGgtMTM0NHEtNDAgMC02OC0yOHQtMjgtNjh2LTEzNDRxMC00MCAyOC02OHQ2OC0yOGg5MjhxNDAgMCA4OCAyMHQ3NiA0OGwyODAgMjgwcTI4IDI4IDQ4IDc2dDIwIDg4eiIvPjwvc3ZnPg==);
        }
        .leaflet-pm-toolbar .leaflet-pm-icon-load {

         background-image: url(data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHdpZHRoPSIxNSIgaGVpZ2h0PSIxNCIgdmlld0JveD0iMCAwIDE1IDE0Ij4KPHBhdGggZD0iTTEzLjkxNCA3LjI3M3EwLTAuMjczLTAuNDE0LTAuMjczaC04LjVxLTAuMzEyIDAtMC42NjggMC4xNjh0LTAuNTU5IDAuNDFsLTIuMjk3IDIuODM2cS0wLjE0MSAwLjE4Ny0wLjE0MSAwLjMxMiAwIDAuMjczIDAuNDE0IDAuMjczaDguNXEwLjMxMiAwIDAuNjcyLTAuMTcydDAuNTU1LTAuNDE0bDIuMjk3LTIuODM2cTAuMTQxLTAuMTcyIDAuMTQxLTAuMzA1ek01IDZoNnYtMS4yNXEwLTAuMzEyLTAuMjE5LTAuNTMxdC0wLjUzMS0wLjIxOWgtNC41cS0wLjMxMiAwLTAuNTMxLTAuMjE5dC0wLjIxOS0wLjUzMXYtMC41cTAtMC4zMTItMC4yMTktMC41MzF0LTAuNTMxLTAuMjE5aC0yLjVxLTAuMzEyIDAtMC41MzEgMC4yMTl0LTAuMjE5IDAuNTMxdjYuNjY0bDItMi40NjFxMC4zNDQtMC40MTQgMC45MDYtMC42ODR0MS4wOTQtMC4yN3pNMTQuOTE0IDcuMjczcTAgMC40ODQtMC4zNTkgMC45MzdsLTIuMzA1IDIuODM2cS0wLjMzNiAwLjQxNC0wLjkwNiAwLjY4NHQtMS4wOTQgMC4yN2gtOC41cS0wLjcxOSAwLTEuMjM0LTAuNTE2dC0wLjUxNi0xLjIzNHYtNy41cTAtMC43MTkgMC41MTYtMS4yMzR0MS4yMzQtMC41MTZoMi41cTAuNzE5IDAgMS4yMzQgMC41MTZ0MC41MTYgMS4yMzR2MC4yNWg0LjI1cTAuNzE5IDAgMS4yMzQgMC41MTZ0MC41MTYgMS4yMzR2MS4yNWgxLjVxMC40MjIgMCAwLjc3MyAwLjE5MXQwLjUyMyAwLjU1MXEwLjExNyAwLjI1IDAuMTE3IDAuNTMxeiI+PC9wYXRoPgo8L3N2Zz4K);
        }
      #coords:before {content: "Coords: "}
      .pm-textarea { padding-left: 3px; }
      .pm-textarea:focus { padding-left: 7px; }
	</style>
    <script>

        const map = L.map('map', {
            crs: L.CRS.Simple,
            minZoom: -2,
            maxZoom: 7,
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
            document.getElementById('coords').innerText = Math.round(event.latlng.lng)+":"+Math.round(event.latlng.lat);
        });


        // Icons
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

        var facadeGroup = [];
        var deathGroup = [];

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
            console.info(e);
        }).addTo(edraLayer);

        L.polygon([xy(609,1383), xy(585,1365), xy(582, 1321), xy(558, 1317), xy(564, 1292),
            xy(616, 1300), xy(653, 1369), xy(653, 1407), xy(622,1412)],{color:'blue', weight: 1}).addTo(moorvaLayer);

        function loadLayerModal(type, data = {}) {
            var modal = $('#dataModal');
            var method = 'GET';
            var url = '{{ route('map.layers.index') }}';
            var successFunc = null;
            var send = {};
            switch(type) {
                case 'load' :
                    title = 'load';
                    url = '{{ route('map.layers.index') }}';
                    break;
                case 'save' :
                    title = 'save';
                    url = '{{ route('map.layers.create') }}';
//
                    break;

                case 'show' :
                    url = data.url;
                    successFunc = function(data) {
                        if (data.data) {
                            drawLayer = L.geoJSON(JSON.parse(data.data)).addTo(map);
                            $('#dataModal').modal('hide');
                        }
                    }
                    break;
                case 'delete' :
                    url = data.url;
                    method = 'DELETE';
                    successFunc = function(data) {
                        $(modal).modal('hide');
                        loadLayerModal('load');
                    }
                    break;

                case 'store' :
                    var fg = L.featureGroup();
                    drawLayer.eachLayer((layer)=>{
                        //if(layer instanceof L.Path || layer instanceof L.Marker){
                            console.log(layer);
                            fg.addLayer(layer);
                    // }
                    });

                    send = {
                        name: data.name,
                        layer: JSON.stringify(fg.toGeoJSON())
                    };
                    method = 'POST';
                    successFunc = function(data) {
                        $(modal).modal('hide');
                    }
                    break;
                default :
                    alert('unknown type: '+type);
                    return;
            }


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: method,
                url: url,
                dataType: 'json',
                data: send,
                success: function (data) {
                    console.log(data);
                    if (!successFunc) {
                        $(modal).modal();
                        $(modal).find('.modal-title').text(title);
                        $(modal).find('.modal-body').html(data.html);
                    } else {
                        successFunc(data);
                    }
                },
                error: function (data) {
                    alert('There was an error (see console for details)');
                    console.log('error');
                    console.log(data);
                }
            });
        }

        function saveDrawnLayer() {
            event.preventDefault();
            var name = $(event.target).find('[name=name]').val();
            loadLayerModal('store',{name:name,url:'{{ route('map.layers.store') }}'});
        }

        var drawLayer = L.layerGroup([]).addTo(map);
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
                    {
                        'label': 'Custom', selectAllCheckbox: true,
                        'children': [
                            {'label':'Draw', 'layer': drawLayer }
                        ]
                    },
                ]
            }).addTo(map);

        // Edit
        map.pm.addControls({
            drawControls: true,
            editControls: true,
            optionsControls: true,
            customControls: true,
            oneBlock: false,
//             positions: {
//                 custom: 'bottomright'
//             }
        });

        map.pm.setGlobalOptions({continueDrawing:false, layerGroup: drawLayer});
        map.pm.setPathOptions({
            color: '#124240',
            fillColor: 'white',
            fillOpacity: 0.4,
        });
        map.pm.Toolbar.createCustomControl(
            {
                name: 'Delete',
                title: 'Delete All Drawn Layers',
                block: 'edit',
                onClick: function(){drawLayer.clearLayers();},
                className: 'leaflet-pm-icon-trash'
            }
        );
        map.pm.Toolbar.createCustomControl(
            {
                name: 'Load',
                title: 'Load drawn layer',
                block: 'custom',
                onClick: function() {loadLayerModal('load');},
                className: 'leaflet-pm-icon-load'
            }
        );
        map.pm.Toolbar.createCustomControl(
            {
                name: 'Save',
                title: 'Save drawn layer',
                block: 'custom',
                onClick: function() {loadLayerModal('save');},
                className: 'leaflet-pm-icon-save'
            }
        );
        var drawnShapesJson = [];
        var tmp;
        function updateDrawLayerData(e) {
            console.log(e);
            var layer = e.layer;
            tmp = layer;
            console.log(layer);
            drawnShapesJson.push({
                instance:layer.constructor.name,
                data:layer.toGeoJSON()}
            );
        }
        map.on('pm:create', function(e) {
            updateDrawLayerData(e);
        });


    </script>
@endsection
