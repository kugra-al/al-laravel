@extends('layouts.app')

@section('content')
<div class="container" style="max-width:90%">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Items') }}</div>

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
    <div class="modal" id="itmFileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="width:800px;">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onClick="$('#itmFileModal').hide()"></button>
                </div>
                <div class="modal-body">
                    <p>Modal body text goes here.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onClick="$('#itmFileModal').hide()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        #items-table { display: none; }
        #main-loader {text-align: center; }
        /* for colvis list.  Sketchy - something else might use this selector */
        .dt-button-collection { overflow: auto; }
        .dt-button-collection .dropdown-menu { height: 300px; }
        .dropdown-item.active, .dropdown-item:active { background-color: #d7d7d7; }
        #items-table_wrapper { overflow-y: auto; }
    </style>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        // Loads after initComplete event on DataTable
        function postInitFuncs() {
            var table = window.LaravelDataTables['items-table'];
            $('#items-table tbody').on('click', 'tr button.view-itm-file', function() {
                var data = table.row( $(this).parents('tr').first() ).data();
                loadItmFile(data['fullpath']);
            });
            $('#main-loader').slideUp();
            $('#items-table').fadeIn();
        }
        function loadItmFile (file) {
           $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: 'POST',
                url: '/index.php/items/loadfile',
                data: {file: file},
                dataType: 'json',
                success: function (data) {
                    $('#itmFileModal').show();
                    $('#itmFileModal').find('.modal-body').html("<pre style='background:#111; color: #1af21a'>"+data+"</pre>");
                    $('#itmFileModal').find('.modal-title').text('File: '+file);
                    console.log(data);
                },
                error: function (data) {
                    alert('There was an error (see console for details)');
                    console.log('error');
                    console.log(data);
                }
            });
            console.log(file);
        }
    </script>
@endpush
