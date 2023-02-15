@extends('layouts.app')

@section('content')
    <div class="container" style="max-width:90%">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __(ucfirst($type)) }}</div>

                    <div class="card-body">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="dataModal" tabindex="-1">
        <div class="modal-dialog" style="min-width: 80%">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="$('#dataModal').modal('hide')"></button>
                </div>
                <div class="modal-body">
                    <p>Modal body text goes here.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="$('#dataModal').modal('hide')">Close</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        pre.code {
            white-space: pre-wrap;
            background: #181818;
            color: #0b0;
        }
        pre.code::before {
            counter-reset: listing;
        }
        pre.code code {
            counter-increment: listing;
            display: block;
        }
        pre.code code::before {
            content: counter(listing) ". ";
            display: inline-block;
            width: 8em;
            padding-left: auto;
            margin-left: auto;
            text-align: right;
        }
    </style>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        // Loads after initComplete event on DataTable
        function postInitFuncs() {
             var table = window.LaravelDataTables['dataTableBuilder'];
             console.log(table);
             $('#dataTableBuilder tbody').on('click', 'tr button.view-data', function() {
                var data = table.row( $(this).parents('tr').first() ).data();
                loadData(data['id']);
             });
            console.log('ok');
        }

        function loadData(id) {
            console.log("Loading data for "+id);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: 'POST',
                url: window.location.pathname+"/data",
                data: {id: id},
                dataType: 'json',
                success: function (data) {
                    $('#dataModal').modal("show");
                    var lines = data.data.parsed;

                    $('#dataModal').find('.modal-body').html("");
                    $('#dataModal').find('.modal-body').append("<h4>Original data from /perms/perm_obj/"+data.filename+"</h4><textarea style='width:100%; height: 150px'>"+data.data.original+"</textarea>");
                    var linenum = 2;
                    for(const [key,line] of Object.entries(lines)) {
                        var pre = document.createElement('pre');
                        $(pre).prop('id', 'file-code');
                        $(pre).addClass('code');

                        var lineJson;
                        try {
                            var lineJson = JSON.parse(line);
                            lineJson = JSON.stringify(lineJson,null,2);
                            lineJson = lineJson.split("\n");
                            $("#dataModal").find(".modal-body").append("<b>Parsed from line "+linenum+"</b>");
                            console.log(lineJson);
                        } catch (e) {
                            $("#dataModal").find(".modal-body").append("<b>Failed to parse line</b>");
                            $(pre).removeClass('code');
                            lineJson = line.split('\n');
                        }
                        linenum++;
                        for(x = 0; x < lineJson.length; x++) {
                            $(pre).append("<code>"+lineJson[x]+"</code>");
                        }

                        $('#dataModal').find('.modal-body').append(pre);

                    }
                    if (Object.keys.length == 0) {
                        $('#dataModal').find('.modal-body').append("No data to show");
                    }

                    //$('#dataModal').find('.modal-body').html("<a target='_blank' style='color: #3700ce' href='"+file.replace('/obj/','https://github.com/Amirani-al/Accursedlands-obj/blob/master/')+"'>View "+file+" on github</a><br/>");
                   // $('#dataModal').find('.modal-title').text('File: '+file);
                },
                error: function (data) {
                    alert('There was an error (see console for details)');
                    console.log('error');
                    console.log(data);
                }
            });
        }
    </script>
@endpush
