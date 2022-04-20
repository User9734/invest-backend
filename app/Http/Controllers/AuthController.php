<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Operation;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
        if ($request->has([
            'nom',
            'prenoms',
            'phone',
            'email',
            'lieu_habitation',
            'password',
            'role_id'
        ])) {
            if ((Role::find($request->role_id) != null)) {
                $user = User::create([
                    'nom' => $request->nom,
                    'prenoms' => $request->prenoms,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'lieu_habitation' => $request->lieu_habitation,
                    'password' => Hash::make($request->password),
                ]);
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $request->role_id,
                ]);
                return response()->json([
                    'status' => 'true',
                    'data' => $user,
                    'message' => 'Successfully created user!'
                ], 201);
            } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Wrong credentials.'
                ], 401);
            }
            
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'user not created! Fill all fields!'
            ], 201);
        }
        
        
    }

    public function signupMobile(Request $request)
    {
        if ($request->has([
            'nom',
            'prenoms',
            'phone',
            'email',
            'lieu_habitation',
            'password',
        ])) {
                $user = User::create([
                    'nom' => $request->nom,
                    'prenoms' => $request->prenoms,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'lieu_habitation' => $request->lieu_habitation,
                    'password' => Hash::make($request->password),
                ]);
                $role = Role::where('libelle', 'investisseur')->first();
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ]);
                return response()->json([
                    'status' => 'true',
                    'data' => $user,
                    'message' => 'Successfully created user!'
                ], 201);
            
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'user not created! Fill all fields!'
            ], 201);
        }
        
        
    }
  
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function loginByAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Identifiants incorrects'
            ], 401);
        $user = $request->user();
        $roles = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('roles.*', 'users.id')
            ->where('users.id', $user->id)
            ->get();
            $tab = [];
            foreach($roles as $key => $role){
                array_push($tab,$role->libelle);
            };
        $user->tab = $tab;
        if (in_array('admin', $user->tab) || in_array('fournisseur', $user->tab)) {
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            if ($request->remember_me)
                $token->expires_at = Carbon::now()->addWeeks(1);
            $token->save();
            return response()->json([
                'status' => 'true',
                'user' => $user,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString()
            ]);
        }
        else {
            return response()->json([
                'message' => 'Non autorisé'
            ], 401);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Identifiants incorrects'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        
        return response()->json([
            'status' => 'true',
            'user' => $user,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'status' => 'true',
            'message' => 'Successfully logged out'
        ]);
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}