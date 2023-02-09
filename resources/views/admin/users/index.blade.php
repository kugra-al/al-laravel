@extends('layouts.admin')

@section('admin-content')
    @if(isset($users))
        <table class="table task-table">
            <thead>
                <th>ID</th>
                <th>Name/th>
                <th>Email</th>
                <th>Roles</th>
                <th>Action</th>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ implode(", ", $user->getRoleNames()->toArray()) }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-info">Edit</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

        <form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-success">Create User</a>
        </form>

    </div>
@endsection
