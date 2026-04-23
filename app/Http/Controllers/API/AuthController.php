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
                'role_as'=>$request->role_as,
                'password'=>Hash::make($request->password),
            ]);

           // $user->createToken('token-name', ['server:update'])->plainTextToken;
        //    $token = $user->createToken($user->email.'_Token', now()->addWeek())->plainTextToken;
            $token = $user->createToken($user->email.'_Token', [''], now()->addDays(2))->plainTextToken;

            return response()->json([
                'status'=>200,
                'id'=>$user->id,
                'role_as'=>$request->role_as,
                'name'=>$user->name,
                'email'=>$user->email,
                'token'=>$token,
                'message'=>'Registered Successfull',
            ]);
        }
    }

   public function fetchUserList()
    {
        $users = User::where('status', 0)->get();

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
                    $role_as = $user->role_as;

                    $token = $user->createToken($user->email.'_AdminToken', ['server:admin'], now()->addDays(2))->plainTextToken;
                } else {
                    $role_as = $user->role_as;
                    $token = $user->createToken($user->email.'_Token', [''], now()->addDays(2))->plainTextToken;
                }
            

            return response()->json([
                'status'=>200,
                'id'=>$user->id,
                'username'=>$user->name,
                'name'=>$user->name,
                'email'=>$user->email,
                'role_as'=>$role_as,
                'token'=>$token,
                'message'=>'Logged in Successfull',
            ]);

            }

        }
    }

    public function logout() {
    //  auth()->user()->tokens()->delete();
     auth('sanctum')->user()->tokens()->delete();
        return response()->json([
        'status'=>200,
        'message'=>'Log out Successfully'
        ]);
    }
}


