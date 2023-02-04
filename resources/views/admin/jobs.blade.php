@extends('layouts.admin')

@section('admin-content')
    @if(isset($jobs))
        <table class="table task-table">
            <thead>
                <th>Filename</th>
                <th>Action</th>
            </thead>
            <tbody>
            @foreach($jobs as $job)
                <tr>
                    <td>{{ $job }}</td>
                    <td>
                        <form action="{{ route('admin.jobs.run') }}" method="POST">
                            @csrf
                            <input type="hidden" name="job" value="{{ $job }}">
                            <button class="btn btn-warning">Run</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
