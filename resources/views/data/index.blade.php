@extends('layouts.app')

@section('content')
    <div class="container" style="max-width:90%">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __($type) }}</div>

                    <div class="card-body">
    @if(isset($data) && sizeof($data))
        <table class="table task-table">
            <thead>
                @foreach(array_keys($data->first()->toArray()) as $key)
                    <th>{{ $key }}</th>
                @endforeach
            </thead>
            <tbody>
                @foreach($data as $line)
                    <tr>
                        @foreach($line->toArray() as $key=>$value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $data->links() }}
    @else
        No data found
    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
