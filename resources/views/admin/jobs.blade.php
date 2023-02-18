@extends('layouts.admin')

@section('admin-content')
    @if(isset($jobs))
        <table class="table task-table" id="jobTable">
            <thead>
                <th>Job</th>
                <th style='width: 25%'>Description</th>
                <th>Est. Job Time</th>
                <th>Type</th>
                <th>Group</th>
                <th>Source</th>
                <th>Action</th>
            </thead>
            <tbody>
            @foreach($jobs as $job=>$jobData)
                <tr class="{{ $jobData['type'] }}">
                    <td>{{ $job }}</td>
                    <td>{!! $jobData['desc'] !!}</td>
                    <td>{{ $jobData['time'] }}</td>
                    <td>{{ $jobData['type'] }}</td>
                    <td>{{ $jobData['group'] }}</td>
                    <td>
                        @if(isset($jobData['sources']))
                            @foreach($jobData['sources'] as $key=>$value)
                                <b>{{ $key }}</b>:
                                @if(is_array($value))
                                    <ul>
                                    @foreach($value as $v)
                                        <li>{{ $v }}</li>
                                    @endforeach
                                    </ul>
                                @else
                                    {{ $value }}
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.jobs.run') }}" method="POST" onSubmit="if(!confirm('Are you sure?')){return false;}">
                            @csrf
                            @switch($job)
                                @case('fetch-repo')
                                    @if (isset($jobData['repos']) && sizeof($jobData['repos']))
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="inputGroupSelect01">Repo</label>
                                            </div>

                                            <select name="repo" class="custom-select form-control" id="inputGroupSelect01">
                                                <option>Select..</option>
                                                @foreach($jobData['repos'] as $repo)
                                                    <option value="{{ $repo }}">{{ $repo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    @if (isset($jobData['branches']) && sizeof($jobData['branches']))
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="inputGroupSelect02">Branch</label>
                                            </div>
                                            <select name="branch" class="form-control custom-select" id="inputGroupSelect02">
                                                <option>Select..</option>
                                                @foreach($jobData['branches'] as $branch)
                                                    <option value="{{ $branch }}">{{ $branch }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    @break
                                @case('reset-table')
                                    @if(isset($jobData['tables']))
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="inputGroupSelect03">Table</label>
                                            </div>
                                            <select name='table' class='form-control custom-select' id="inputGroupSelect03">
                                                <option>Select..</option>
                                            @foreach($jobData['tables'] as $table)
                                                <option value='{{ $table }}'>{{ $table }}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    @break
                            @endswitch

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
    <style>
        #jobTable tr.fetch { background: #799db7; }
        #jobTable tr.process { background: var(--bs-green); }
        #jobTable tr.reset { background: #d78d8f; }
        #jobTable tr.write { background: #73cbb1; }
    </style>
@endsection
