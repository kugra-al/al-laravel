<div class="container">
    <div class="row">
        <div class="col-sm-12">
            @if(isset($layers))
                <table class="table task-table">
                    <thead>
                        <th>ID</th>
                        <th>User</th>
                        <th>Name</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                @foreach($layers as $layer)
                    <tr>
                        <td>{{ $layer->id }}</td>
                        <td>{{ $layer->user_id }}</td>
                        <td>{{ $layer->name }}</td>
                        <td>
                            <form method="POST" onsubmit="loadDrawnLayer({{ $layer->id }})">
                                <button type="submit" class="btn btn-sm btn-success">Load</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
