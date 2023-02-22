<div class="container">
            @if(isset($layers))
                @foreach($layers as $name=>$collection)
                    <div class="row">
                        <div class="col-sm-12">
                    @if($collection->count())
                        <h5>{{ $name }}</h5>
                        <div style="height:400px; overflow: scroll">
                            <table class="table task-table">
                                <thead>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Name</th>
                                    <th>Desc</th>
                                    <th>Action</th>
                                </thead>
                                <tbody>
                            @foreach($collection as $layer)
                                <tr>
                                    <td>{{ $layer->id }}</td>
                                    <td>{{ $layer->user->name }}</td>
                                    <td>{{ $layer->name }}</td>
                                    <td>{!! $layer->desc !!}</td>
                                    <td>
                                        <button type="submit" class="btn btn-sm btn-success" onclick="loadLayerModal('show',{url:'{{ route('map.layers.show', $layer->id) }}'})">Load</button>
                                        @if($layer->user->id == Auth::user()->id)<button onclick="if(confirm('Are you sure you want to delete this layer?')){loadLayerModal('delete',{url:'{{ route('map.layers.destroy', $layer->id) }}'});}" type="submit" class="btn btn-sm btn-danger">Delete</button>@endif

                                    </td>
                                </tr>
                            @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p>No layers found for: {{ $name }}</p>
                    @endif
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
