<div class="container">
    <div class="row">
        <div class="col-sm-12">
            @if(isset($layer) && $layer)
                <form method="POST" onsubmit="updateDrawnLayer()">
                    @csrf
                    <input name="id" type="hidden" value="{{ $layer->id }}">
                    <input name="name" class="form-control" type="text" value="{{ $layer->name }}">
                    <button type="submit" class="btn btn-success">Save</button>
                </form>
            @endif
        </div>
    </div>
</div>
