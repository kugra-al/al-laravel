<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <form method="POST" class="row" onsubmit="saveDrawnLayer()">
                @csrf
                <div class="mb-3">
                    <label>Name</label>
                    <input name="name" class="form-control" type="text" placeholder="save" @if($name) value="{{ $name }}" @else value="layer_{{ time() }}" @endif>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="desc" class="form-control"></textarea>
                </div>
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-success">Save New Layer</button>
                </div>
            </form>
        </div>
    </div>
</div>
