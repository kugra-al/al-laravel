@extends('layouts.admin')

@section('admin-content')
    @if(isset($jobs))
        <table class="table task-table">
            <thead>
                <th>Job</th>
                <th>Description</th>
                <th>Est. Job Time</th>
                <th>Action</th>
            </thead>
            <tbody>
            @foreach($jobs as $job=>$jobData)
                <tr>
                    <td>{{ $job }}</td>
                    <td>{!! $jobData['desc'] !!}</td>
                    <td>{{ $jobData['time'] }}</td>
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
        <p>Don't click any of the buttons here unless you are sure you know what you are doing. Doing things out of order can break things.</p>
        <p>If something doesn't work, restart queue:worker with cli CMD `php artisan queue:work`</p>
        <p>Later will add something to track running jobs and fail/success</p>
    @endif
@endsection
