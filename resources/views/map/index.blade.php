@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin=""/>
    <link rel="stylesheet" href="/css/MarkerCluster.css"/>
    <link rel="stylesheet" href="/css/MarkerCluster.Default.css"/>
    <script src="/js/leaflet.js"></script>
    <script src="/js/leaflet.markercluster.js"></script>


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
    <script src="/js/leaflet-geoman.min.js"></script>

    <link rel="stylesheet" href="/css/leaflet-geoman.css" />
    <link rel="stylesheet" href="/css/L.Control.Layers.Tree.css"/>
    <link rel="stylesheet" href="/css/leaflet-overrides.css" />

    <script>
        // Map setup
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


        // load in overlays - todo load via api
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

        // Setup overlays
        // facades
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
        // Deaths
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

        // Perms
        var buildingLayer = L.markerClusterGroup();
        var signpostLayer = L.markerClusterGroup();
        var destroyedLayer = L.markerClusterGroup();
        var unfinishedLayer = L.markerClusterGroup();
        var otherPermLayer = L.markerClusterGroup();
        var tentLayer = L.markerClusterGroup();
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

        // Layer management
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
                    send = currentDrawLayer;
//
                    break;

                case 'show' :
                    url = data.url;
                    successFunc = function(data) {
                        if (data.data) {
                            drawLayer.clearLayers();
                            var jsonData = JSON.parse(data.data);
                            importGeoJSON(jsonData, drawLayer);
                            if (data.id)
                                currentDrawLayer.id = data.id;
                            currentDrawLayer.name = data.name;

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
                    send = {
                        name: data.name,
                        layer: generateGeoJson(drawLayer)
                    };
                    method = 'POST';
                    successFunc = function(data) {
                        $(modal).modal('hide');
                    }
                    break;
                case 'update' :
                    send = {
                        name: data.name,
                        id: data.id,
                        layer: generateGeoJson(drawLayer)
                    };
                    url = data.url;
                    method = 'PATCH';
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
        // doesn't save text, circle, circle marker, rectangle
            event.preventDefault();
            var name = $(event.target).find('[name=name]').val();
            loadLayerModal('store',{name:name,url:'{{ route('map.layers.store') }}'});
        }

        function updateDrawnLayer() {
            event.preventDefault();
             var name = $(event.target).find('[name=name]').val();
             var id = $(event.target).find('[name=id]').val();
            loadLayerModal('update',
                {
                    id: id,
                    name:name,
                    url:'{{ route('map.layers.index') }}'+"/update/"
                }
            );
        }

        var currentDrawLayer = {};
        // Draw layers and add controls
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

        // Add Edit controls
        map.pm.addControls({
            drawControls: true,
            editControls: true,
            optionsControls: true,
            customControls: true,
            oneBlock: false,
            //drawText: false // disabled until save works
        });


        map.pm.setGlobalOptions({continueDrawing:false, layerGroup: drawLayer, snappable: false});
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
                onClick: () => {drawLayer.clearLayers();},
                toggle: false,
                className: 'leaflet-pm-icon-trash'
            }
        );
        map.pm.Toolbar.createCustomControl(
            {
                name: 'Load',
                title: 'Load drawn layer',
                block: 'custom',
                onClick: () => {loadLayerModal('load');},
                toggle: false,
                className: 'leaflet-pm-icon-load'
            }
        );
        map.pm.Toolbar.createCustomControl(
            {
                name: 'Save',
                title: 'Save drawn layer',
                block: 'custom',
                onClick: () => {loadLayerModal('save');},
                toggle: false,
                className: 'leaflet-pm-icon-save'
            }
        );



    function generateGeoJson(layerToGen){
        var fg = L.featureGroup();
        var layers = findLayers(layerToGen);

        var geo = {
            type: "FeatureCollection",
            features: [],
        };
        layers.forEach(function(layer){
            var geoJson = JSON.parse(JSON.stringify(layer.toGeoJSON()));
            if(!geoJson.properties){
                geoJson.properties = {};
            }

            geoJson.properties.options = JSON.parse(JSON.stringify(layer.options));

            if(layer.options.radius){
                var radius =  parseFloat(layer.options.radius);
                if(radius % 1 !== 0) {
                    geoJson.properties.options.radius = radius.toFixed(6);
                }else{
                    geoJson.properties.options.radius = radius.toFixed(0);
                }
            }
            console.log(layer.options);
            if(layer.options.text){
                geoJson.properties.options.text = layer.options.text;
                geoJson.properties.options.textMarker = true;
            }

            if (layer instanceof L.Rectangle) {
                geoJson.properties.type = "rectangle";
            } else if (layer instanceof L.Circle) {
                geoJson.properties.type = "circle";
            } else if (layer instanceof L.CircleMarker) {
                geoJson.properties.type = "circlemarker";
            } else if (layer instanceof L.Polygon) {
                geoJson.properties.type = "polygon";
            } else if (layer instanceof L.Polyline) {
                geoJson.properties.type = "polyline";
            } else if (layer instanceof L.Marker) {
                geoJson.properties.type = "marker";
            } else {
                console.log('unknown type');
                console.log(layer);
            }


            geo.features.push(geoJson);
        });
        console.log(JSON.stringify(geo));
        return JSON.stringify(geo);
    }

    function findLayers(layerToGen) {
        var layers = [];
        layerToGen.eachLayer(layer => {
            if (
                layer instanceof L.Polyline ||
                layer instanceof L.Marker ||
                layer instanceof L.Circle ||
                layer instanceof L.CircleMarker
            ) {
                layers.push(layer);
            }
        });

        // filter out layers that don't have the leaflet-geoman instance
        layers = layers.filter(layer => !!layer.pm);

        // filter out everything that's leaflet-geoman specific temporary stuff
        layers = layers.filter(layer => !layer._pmTempLayer);

        return layers;
    }

    function importGeoJSON(feature, layerToImport){
        var geoLayer = L.geoJSON(feature, {
            style: function (feature) {
                return feature.properties.options;
            },
            pointToLayer: function(feature, latlng){
                switch (feature.properties.type) {
                    case "marker":
                        if (feature.properties.options.textMarker)
                            return new L.Marker(latlng, feature.properties.options);
                        return new L.Marker(latlng);
                    case "circle": return new L.Circle(latlng, feature.properties.options);
                    case "circlemarker": return new L.CircleMarker(latlng, feature.properties.options);
                   // case "textMarker": return new L.
                }
            }
        });

        geoLayer.getLayers().forEach((layer) => {
            if (layer._latlng) {
                var latlng = layer.getLatLng();
            } else {
                var latlng = layer.getLatLngs();
            }
            switch (layer.feature.properties.type) {
                case "rectangle":
                    new L.Rectangle(latlng,  layer.options).addTo(layerToImport);
                    break;
                case "circle":
                        console.log(layer.options)
                    new L.Circle(latlng, layer.options).addTo(layerToImport);
                    break;
                case "polygon":
                    new L.Polygon(latlng, layer.options).addTo(layerToImport);
                    break;
                case "polyline":
                    new L.Polyline(latlng, layer.options).addTo(layerToImport);
                    break;
                case "marker":
                    new L.Marker(latlng, layer.options).addTo(layerToImport);
                    break;
                case "circlemarker":
                    new L.CircleMarker(latlng, layer.options).addTo(layerToImport);
                    break;

            }
        });
    }
    </script>
@endsection
