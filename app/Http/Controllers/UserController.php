<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\Achat;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Operation;
use App\Models\Package;
use App\Models\Rapport;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function GuzzleHttp\Promise\all;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('role')->get();
        
        /* $users->each(function ($user, $id){
            $operations = Operation::where('user_id', $user->id)
                                ->get();
        $user->operations = $operations;
        $roles = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('roles.*', 'users.id')
            ->where('users.id', $user->id)
            ->get();
        $user->roles = $roles;
        }); */
        if ($users != null) {
            
            return response()->json([
                'data' => $users,
                'status' => 'true'
            ]);
        }
        else{
            return response()->json([
                'status' => 'false'
            ]);
        }
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
            'nom' => 'string',
            'prenoms' => 'string',
            'phone' => 'string',
            'email' => 'string|unique:users,email',
            'lieu_habitation' => 'string',
            'password' => 'string',
            'role_id' => 'array' 
        ]);
        
        if ($request->has([
            'nom',
            'prenoms',
            'phone',
            'email',
            'lieu_habitation',
            'password',
            'role_id' 
        ])) {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            $numbers = '0123456789';
            $numbersLength = strlen($numbers);
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $numbers[rand(0, $numbersLength - 1)];
            }
            $user = new User();
            $user->nom = $request->nom;
            $user->prenoms = $request->prenoms;
            $user->phone = $request->phone;
            $user->registre_commerce = $request->registre_commerce;
            $user->code = $randomString;
            $user->email = $request->email;
            $user->lieu_habitation = $request->lieu_habitation;
            $user->password = Hash::make($request->password);
            $user->save();
            $roles = $request->role_id;
            for ($i=0;$i<count($roles);$i++) 
            {
                /* $test = UserRole::where('user_id', $user->id)->where('role_id', $roles[$i])->first(); */
                //if ($test == null) {
                    $usr_role = new UserRole();
                    $usr_role->role_id = $roles[$i];
                    $usr_role->user_id = $user->id;
                    $usr_role->save();
            } /* else {
                echo 'role '. $i .'th already exists';
                    return response()->json([
                        'status' => 'false',
                        'message' => 'user already has the '. $i .'th role added'
                    ]);
                } */
            return response()->json([
                'data' => $user->with('role')->first(),
                'status' => 'true',
                'message' => 'user created successfully'
            ]);
            
            
        }
        else{
            return response()->json([
                'status' => 'false',
                'message' => 'fill all fields correctly.'
            ]);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with('role')->where('id', $id)->first();
        
        if ($user != null) {
            
            return response()->json([
                
                'data' => $user,
                'status' => 'true'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'id introuvable'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    
      public function getSellers()
    {
        $sellers = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('users.*')
            ->where('roles.libelle', 'fournisseur')
            ->where('users.deleted_at', NULL)
            ->get();
        if ($sellers != null) {
            return response()->json([
                'data' => $sellers,
                'status' => 'true'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'id introuvable'
            ]);
        }
    } 

    public function getInvestors()
    {
        $sellers = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('users.*')
            ->where('roles.libelle', 'investisseur')
            ->where('users.deleted_at', NULL)
            ->get();
        if ($sellers != null) {
            return response()->json([
                'data' => $sellers,
                'status' => 'true'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'id introuvable'
            ]);
        }
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
        $user = User::with('role')->where('id', $id)->first();
        $user->nom = $request->nom;
        $user->prenoms = $request->prenoms;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->lieu_habitation = $request->lieu_habitation;
        $user->password = Hash::make($request->password);
        $user->save();
        UserRole::where('user_id', $user->id)->delete();
            $roles = $request->role_id;
            for ($i=0;$i<count($roles);$i++) 
            {
                    $usr_role = new UserRole();
                    $usr_role->role_id = $roles[$i];
                    $usr_role->user_id = $user->id;
                    $usr_role->save();
            }
            if ($user->wasChanged()) {
                return response()->json([
                    'data' => $user,
                    'status' => 'true'
                ]);
            }
            else{
                return response()->json([
                    'status' => 'false'
                ]);
            }
        
    }

    public function profile(Request $request, $id)
    {
        $user = User::find($id);
        $user->nom = $request->nom;
        $user->prenoms = $request->prenoms;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->lieu_habitation = $request->lieu_habitation;
        $user->password = Hash::make($request->password);
        $user->save();
        if ($user->wasChanged()) {
            return response()->json([
                'data' => $user,
                'status' => 'true'
            ]);
        }
        else{
            return response()->json([
                'status' => 'false'
            ]);
        }
    }

    public function getGain($id){
        $packages = Package::where('user_id',$id)->has('user')->get();
        $packages->each(function ($package){
            $package->gain_par_package = $package->cout_acquisition * $package->nb_products;
        });
        $gain_total = $packages->sum('gain_par_package');
        return response()->json([
            'status' => 'true',
            'gain' => $gain_total,
            'data' => $packages
        ]);
    }

    public function getGainInv($id){
        $user =  User::with('souscription')->find($id);
         $user->souscription->each(function ($package){
             $rapport = Rapport::where('achat_id',$package->pivot->id)->get();
             $achat = Achat::find($package->pivot->id);
            $package->rapport = $rapport;
            $package->nb_achetes = $achat->nb_pieces;
            $package->invest = $package->cout_acquisition * $achat->nb_pieces;
            $package->recover = $rapport->sum('cout');

        }); 
        $gain = $user->souscription->sum('invest');
        $recover = $user->souscription->sum('recover');
        // $gain = $packages->sum('gain');
        return response()->json([
            'status' => 'true',
            'invest' => $gain,
            'received' => $recover,
            'data' => $user
        ]);
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
        $test1 = Operation::all();
        $test3 = Achat::all();
        if ($test1->contains('user_id', $user->id) || $test3->contains('user_id', $user->id)) {
            return response()->json([
                'status' => 'not deleted. already used somewhere'
            ]);
        } else {
            $user->delete($id);
        if ($user->deleted_at != null) {
            return response()->json([
                'status' => 'true'
            ]);
        }
        else {
            return response()->json([
                'status' => 'false'
            ]);
        }
        }
        
    }
}
