<div class="container">
    <div class="row">
        <div class="col-sm-12">
            @if(isset($layers))
                @foreach($layers as $name=>$collection)
                    @if(!$collection->count())
                        <p>No layers found for: {{ $name }}</p>
                        @continue
                    @endif
                    <h5>{{ $name }}</h5>
                    <table class="table task-table">
                        <thead>
                            <th>ID</th>
                            <th>User</th>
                            <th>Name</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                    @foreach($collection as $layer)
                        <tr>
                            <td>{{ $layer->id }}</td>
                            <td>{{ $layer->user->name }}</td>
                            <td>{{ $layer->name }}</td>
                            <td>
                                <button type="submit" class="btn btn-sm btn-success" onclick="loadLayerModal('show',{url:'{{ route('map.layers.show', $layer->id) }}'})">Load</button>
                                @if($layer->user->id == Auth::user()->id)<button onclick="if(confirm('Are you sure you want to delete this layer?')){loadLayerModal('delete',{url:'{{ route('map.layers.destroy', $layer->id) }}'});}" type="submit" class="btn btn-sm btn-danger">Delete</button>@endif

                            </td>
                        </tr>
                    @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif
        </div>
    </div>
</div>
