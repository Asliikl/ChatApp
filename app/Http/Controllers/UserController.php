<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Chat;
 use App\Models\User;
 use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $userId = session('LoggedUserInfo');
        $user = User::find($userId);
    
        if (!$user) {
            return redirect('user/login')->with('fail', 'You must be logged in to update the profile');
        }
    
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'bio' => 'nullable|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $user->name = $request->name;
        $user->phone_number = $request->phone_number;
        $user->bio = $request->bio;
    
        if ($request->hasFile('picture')) {
            if ($user->picture) {
                Storage::disk('public')->delete($user->picture);
            }
    
            $file = $request->file('picture');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
    
            $user->picture = $path;
        }
    
        $user->save();
    
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
    

    
    public function post() {

        $users = User::all();
        return view('user.post', [
            'users' => $users 
        ]);
    }
    
    public function register() {
        return view("user.register");
    }
    public function login() {
        return view("user.login");
    }
    public function chats()
    {
        $userId = session('LoggedUserInfo');
        $LoggedUserInfo = User::find($userId);
        
        if (!$LoggedUserInfo) {
            return redirect('user/login')->with('fail', 'You must be logged in to access the dashboard');
        }

        if ($LoggedUserInfo->role == 'seller') {
            $users = User::where('role', 'buyer')->get();
        } else {
            $users = User::where('role', 'seller')->get();
        }
        
        return view('user.chats', [
            'LoggedUserInfo' => $LoggedUserInfo,
            'users' => $users, 
        ]);
    }

    
    public function edit()
    {
        
        $userId = session('LoggedUserInfo');
        $LoggedUserInfo = User::find($userId);
    
        if (!$LoggedUserInfo) {
            return redirect('user/login')->with('fail', 'You must be logged in to access the dashboard');
        }
     
       
    return view('user.profileedit', [
        'LoggedUserInfo' => $LoggedUserInfo,
       
    ]);
    }
    
    public function profileview()
    {
        
        $userId = session('LoggedUserInfo');
        $LoggedUserInfo = User::find($userId);
    
        if (!$LoggedUserInfo) {
            return redirect('user/login')->with('fail', 'You must be logged in to access the dashboard');
        }
     
       
    return view('user.profileview', [
        'LoggedUserInfo' => $LoggedUserInfo,
      
    ]);
    }
    
    
    public function dashboard()
    {
        $userId = session('LoggedUserInfo');
    
        if (!$userId) {
            return redirect('user/login')->with('fail', 'You must be logged in to access the dashboard');
        }
    
        $LoggedUserInfo = User::find($userId);
    
        return view('user.dashboard', [
            'LoggedUserInfo' => $LoggedUserInfo,
       
        ]);
    }
    
    
    public function check(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:5|max:12'
        ]);

        $userInfo = User::where('email', $request->email)->first();

        if (!$userInfo) {
            return back()->withInput()->withErrors(['email' => 'Email not found']);
        }
        if ($userInfo->status === 'inactive') {
            return back()->withInput()->withErrors(['status' => 'Your account is inactive']);
            }

        if (!Hash::check($request->password, $userInfo->password)) {
            return back()->withInput()->withErrors(['password' => 'Incorrect password']);
        }

        session([
            'LoggedUserInfo' => $userInfo->id,
            'LoggedUserName' => $userInfo->name,  
        ]);
        return redirect()->route('user.dashboard');
    }


    public function logout()
    {
         if (session()->has('LoggedUserInfo')) {
             session()->forget('LoggedUserInfo');
        }
        session()->flush();

         return redirect()->route('user.dashboard');
    }
    
 
    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|regex:/^\S*$/',
            'role' => 'required|in:buyer,seller',  
        ], [
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
            'role.in' => 'Role must be either buyer or seller.',
        ]);
    
         
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,   
        ]);
    
        return redirect()->route('user.login')->with('success', 'User created successfully!');
    }
    

}