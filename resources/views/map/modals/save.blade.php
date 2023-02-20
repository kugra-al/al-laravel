<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <form method="POST" onsubmit="saveDrawnLayer()">
                @csrf
                <input name="name" class="form-control" type="text" placeholder="save" value="layer_{{ time() }}">
                <button type="submit" class="btn btn-success">Save</button>
            </form>
        </div>
    </div>
</div>
