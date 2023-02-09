<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::paginate(50);
        return view('admin.users.index',['users'=>$users]);
    }

    /**
     * Show the form for creating a  new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'password_confirmation' => 'required_with:password|same:password'
        ]);

        if (User::where('email',$request->get('email'))->first())
           return back()->with("status", "User with email {$request->get('email')} already exists!");

        $user = new User;
        $user->email = $request->get('email');
        $user->name = $request->get('name');
        $user->password = \Hash::make($request->password);
        $user->save();

        return redirect("/admin/users/{$user->id}/edit")->with('status',"User {$user->id} created. Now add roles for user");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        if (!$user)
            return redirect('admin/users')->with('status',"User {$id} not found");
        $roles = Role::all();
        $userRoles = $user->roles()->pluck('name')->toArray();
        return view('admin.users.edit',['user'=>$user, 'roles'=>$roles, 'userRoles'=>$userRoles]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password_confirmation'=>'required_with:password|same:password'
        ]);
        $user = User::find($id);
        if (!$user)
            return redirect('admin/users')->with('status','User not found');
        if ($user->email != $request->get('email'))
            $user->email = $request->get('email');
        if ($user->name != $request->get('name'))
            $user->name = $request->get('name');
        if ($request->get('password') && strlen($request->get('password'))) {
            $passwordHash = \Hash::make($request->get('password'));
            if ($passwordHash != $user->password)
                $user->password = $passwordHash;
        }
        $status = "User vars not updated.  ";
        if ($user->isDirty()) {
            $user->save();
            $status = "User vars updated.  ";
        }
        $userRoles = $user->roles()->get()->pluck('name')->toArray();
        if ($userRoles != $request->get('roles')) {
            $user->syncRoles($request->get('roles'));
            $status .= "User roles updated.";
        } else
            $status .= "User roles not updated.";
        return redirect('admin/users')->with('status',$status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect('admin/users')->with('status',"Deleted user {$id}");
    }
}
