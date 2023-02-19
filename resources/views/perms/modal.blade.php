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
        .modal-body textarea.data { width: 100%; height: 200px; }
        .modal-body table tbody td, .modal-body div.item-row { word-break: break-all; }
        .modal-body div.item-row { padding: 5px; }
</style>
<script>
   function loadPermData(id) {
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
                url: "/perms/data",
                data: {id: id},
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                    $('#dataModal').find('.modal-body').html(data.html);
                    $('#dataModal').find('.modal-title').text(data.title);
                    return;


                },
                error: function (data) {
                    alert('There was an error (see console for details)');
                    console.log('error');
                    console.log(data);
                }
            });
      }
</script>
