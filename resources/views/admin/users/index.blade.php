@extends('layouts.admin')

@section('admin-content')
    @if(isset($users))
        <table class="table task-table">
            <thead>
                <th>ID</th>
                <th>Name/th>
                <th>Email</th>
                <th>Action</th>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-info">Edit</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <div class="card">
        <div class="card-body">
            <form>
                <input type="text" placeholder="Enter email" name="email" class="form-control">
                <button class="btn btn-success">Create User</button>
            </form>
        </div>
    </div>
@endsection
