@extends('layouts.app')

@section('content')
<div class="container">
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

                    @if(isset($items))
                        <table class="table task-table">
                            <thead>
                                <th>ID</th>
                                <th>File</th>
                                <th>Path</th>
                                <th>Itm ID</th>
                                <th>Itm Adj</th>
                                <th>Itm Weight</th>
                                <th></th>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->filename }}</td>
                                        <td>{{ $item->path }}</td>
                                        <td>{{ $item->itm_id }}</td>
                                        <td>{{ $item->itm_adj }}</td>
                                        <td>{{ $item->itm_weight }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
