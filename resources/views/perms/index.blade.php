@extends('layouts.app')

@section('content')
<div class="container" style="max-width:90%">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Perms') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div id="main-loader">
                        <img src="/img/loader-text.gif">
                    </div>
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>
</div>
    <div class="modal" id="dataModal" tabindex="-1">
        <div class="modal-dialog" style="min-width:70%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onClick="$('#dataModal').modal('hide')"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onClick="$('#dataModal').modal('hide')">Close</button>
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
        table.table-bordered.dataTable td { word-break: break-word; }
        .dt-button-collection { overflow: auto; }
        .dt-button-collection .dropdown-menu { height: 300px; }
        .dropdown-item.active, .dropdown-item:active { background-color: #d7d7d7; }
        .modal-body textarea.data { width: 90%; height: 200px; margin-left: 50px; }
        .modal-body table tbody td { word-break: break-all; }
    </style>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        // Loads after initComplete event on DataTable
        function postInitFuncs() {
             var table = window.LaravelDataTables['perms-table'];
             console.log(table);
             $('#perms-table tbody').on('click', 'tr button.view-data', function() {
                var data = table.row( $(this).parents('tr').first() ).data();
                loadData(data['id']);
             });
            $('#main-loader').slideUp();
            $('#perms-table').fadeIn();
        }

        function loadData(id) {
            console.log("Loading data for "+id);
            $('#dataModal').modal("show");
            $('#dataModal').find(".modal-body").html('<img src="/img/loader-text.gif">');
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
                    var lines = data.data;

                    if (Object.keys(data).length == 0) {
                        $('#dataModal').find('.modal-body').html("No data to show");
                        return;
                    }
                    $('#dataModal').find('.modal-body').html("<h4>Data from /perms/perm_objs/"+data.filename+"</h4><textarea class='data'>"+data.data+"</textarea>");
                    $('#dataModal').find('.modal-body').append('<h5>Database values</h5>');
                    var table = "<table class='table table-striped'><thead><th>Key</th><th>Value</th></thead><tbody>";
                    Object.entries(data).forEach(entry => {
                        const [key, value] = entry;
                        if (key != 'data' && key != 'items')
                            table += "<tr><td>"+key+"</td><td>"+value+"</td></tr>";
                    });
                    table += "</tbody></table";
                    $('#dataModal').find('.modal-body').append(table);
                    if (data.items && data.items.length) {
                        $('#dataModal').find('.modal-body').append("<p>Found "+data.num_items+" items with total size of "+data.item_data_size+" in "+data.inventory_location+"</p>");
                        for(var x = 0; x < data.items.length; x++) {
                            var item = data.items[x];
                            $('#dataModal').find('.modal-body').append("<h5>Object: "+item.object+":"+item.pathname+":"+item.short+"</h5>");
                            if (item.touched_by)
                                $('#dataModal').find('.modal-body').append("<b>Touched by: </b><span style='word-break: break-word'>"+item.touched_by+"</span>");
                            $('#dataModal').find('.modal-body').append("<textarea class='data'>"+item.data+"</textarea>");
                        }
                    }

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
