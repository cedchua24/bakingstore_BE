<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    
    public function register(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'name'=>'required|max:191',
            'email'=>'required|email|max:191|unique:users,email',
            'password'=>'required|min:8',
        ]);

        if($validator->fails()) {
            return response()->json([
                'validator_errors'=>$validator->messages(),
            ]);
        }
        else
        {
            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
            ]);

           // $user->createToken('token-name', ['server:update'])->plainTextToken;
           $token = $user->createToken($user->email.'_Token')->plainTextToken;

            return response()->json([
                'status'=>200,
                'id'=>$user->id,
                'name'=>$user->name,
                'email'=>$user->email,
                'name'=>$user->name,
                'token'=>$token,
                'message'=>'Registered Successfull',
            ]);
        }
    }

   public function fetchUserList()
    {
        $users = User::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($users);
    }

  public function destroy(User $user)
    {
        $user = User::find($user->id);
        $user->delete();
        return response()->json($user);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'=>'required|max:191',
            'password'=>'required|min:8',
        ]);

        if($validator->fails()) {
                return response()->json([
                    'validator_errors'=>$validator->messages(),
            ]);
        }

        else
        {
    $user = User::where('email', $request->email)->first();
 
            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'=>401,
                    'message'=>'Invalid Credentials',
                ]);
            }
            
            else {

                if ($user->role_as == 1 ) { //admin
                    $role = 'admin';
                    $token = $user->createToken($user->email.'_AdminToken', ['server:admin'])->plainTextToken;
                } else {
                    $role ='user';
                    $token = $user->createToken($user->email.'_Token', [''])->plainTextToken;
                }
            

            return response()->json([
                'status'=>200,
                'id'=>$user->id,
                'username'=>$user->name,
                'name'=>$user->name,
                'email'=>$user->email,
                'role'=>$role,
                'token'=>$token,
                'message'=>'Logged in Successfull',
            ]);

            }

        }
    }

    public function logout() {
     auth()->user()->tokens()->delete();
        return response()->json([
        'status'=>200,
        'message'=>'Log out Successfully'
        ]);
    }
}


