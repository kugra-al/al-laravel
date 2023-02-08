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
            <div class="modal-content" style="width:1000px;">
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
        textarea.copy {
            position: absolute;
            left: -100%;
        }
    </style>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script defer src="/js/buttons.server-side.js"></script>
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
                   // $('#itmFileModal').find('.modal-body').html("<pre style='background:#111; color: #1af21a'>"+data+"</pre>");
                    var pre = document.createElement('pre');
                    $(pre).prop('id', 'file-code');
                    $(pre).addClass('code');
                    data = data.split("\n");
                    for (x = 0; x < data.length; x++) {
                        if (data[x].length)
                            $(pre).append("<code>"+data[x]+"</code>");
                    }
                    $('#itmFileModal').find('.modal-body').html("<a target='_blank' style='color: #3700ce' href='"+file.replace('/obj/','https://github.com/Amirani-al/Accursedlands-obj/blob/master/')+"'>View "+file+" on github</a><br/>");
                    $('#itmFileModal').find('.modal-body').append("<button class='btn btn-info' id='copyButton' onClick='copyFunction();'>Copy Text to clipboard</button>");
                    $('#itmFileModal').find('.modal-body').append(pre);
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

        function copyFunction() {
            const copyText = document.getElementById("file-code").textContent;
            const textArea = document.createElement('textarea');
            $(textArea).addClass('copy');
            textArea.textContent = copyText;
            document.body.append(textArea);
            textArea.select();
            document.execCommand("copy");
            $('#copyButton').text('Copied');
            $('#copyButton').prop('disabled',true);
            textArea.element.remove();
        }
    </script>
@endpush
