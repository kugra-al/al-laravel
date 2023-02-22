<div class="container">
    <div class="row">
        <div class="col-sm-12">
            @if(isset($layer) && $layer)
                <form method="POST" class="row" onsubmit="updateDrawnLayer()">
                    @csrf
                    <input name="id" type="hidden" value="{{ $layer->id }}">
                    <div class="mb-3">
                        <label>Name</label>
                        <input name="name" class="form-control" type="text" value="{{ $layer->name }}">
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="desc" class="form-control">{!! $layer->desc !!}</textarea>
                    </div>
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-warning">Save Existing Layer</button>
                        <button type="submit" class="btn btn-success" name="new-layer" value="new">Save as New Layer</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
