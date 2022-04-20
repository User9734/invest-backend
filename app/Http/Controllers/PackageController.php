<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\User;
use App\Models\Type;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $packages = Package::with('sell')->with('user')->get();
        $packages->each(function($package){
            $type = Type::find($package->type_id);
            $package->type = $type;
        });
        return response()->json([
            'data' => $packages,
            'status' => 'true'
        ]);
    }

    public function getPublished()
    {
        $packages = Package::with('sell')->where('publie', 1)->get();
        $packages->each(function($package){
            $user = User::find($package->user_id);
            $type = Type::find($package->type_id);
            $package->user = $user;
            $package->type = $type;
        });
        return response()->json([
            'data' => $packages,
            'status' => 'true'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        
        $sellers = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('users.*')
            ->where('roles.libelle', 'fournisseur')
            ->get();
        $user = User::find($request->user_id);
        $type = Type::find($request->type_id);
        
        if ($sellers->contains('id', $user->id)) {
            if ($type != null || $user != null) {
                if ($request->has([
                    'cout_acquisition',
                    'nb_products',
                    'cout_vente',
                    'nb_jours',
                    'user_id',
                    'type_id',
                ]) ) {
                    $package = new Package();
                        $package->cout_acquisition = $request->cout_acquisition;
                        $package->cout_vente = $request->cout_vente;
                        $package->gain = intval($request->cout_vente) - intval($request->cout_acquisition);
                        $package->nb_products = $request->nb_products;
                        $package->nb_jours = $request->nb_jours;
                        $package->user_id = $request->user_id;
                        $package->type_id = $request->type_id;
                        $package->libelle = $request->libelle;
                        $package->save();
                    return response()->json([
                        'data' => $package,
                        'status' => 'true'
                    ]);
                }
                else{
                    return response()->json([
                        'status' => 'false'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'L\'identifiant de l\'utilisateur ou celui du type de package est introuvable'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'L\'utilisateur doit être un fournisseur'
            ]);
        }
        
        
    }

    public function getSellerPackages($id)
    {
        $sellers = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('users.*')
            ->where('roles.libelle', 'fournisseur')
            ->get();
        if ($sellers->contains('id',$id)) {
                $packages = DB::table('packages')->where('user_id', $id)
                                    ->get();
            if ($packages !=null) {
                return response()->json([
                    'data' => $packages,
                    'status' => 'true'
                ]);
            }
            else{
                return response()->json([
                    'status' => 'false'
                ]);
            }
        }
        else{
            return response()->json([
                'status' => 'false',
                'message' => 'Cet utilisateur n\'est pas un fournisseur'
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
        $package = Package::find($id);
        if ($package != null) {
            $user = User::find($package->user_id);
            $type = Type::find($package->type_id);
            $package->user = $user;
            $package->type = $type;
            return response()->json([
                'data' => $package,
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
        
        $user = User::find($request->user_id);
        $type = Type::find($request->type_id);
        if ($type != null && $user != null) {
            $package = Package::find($id);
            $package->cout_acquisition = $request->cout_acquisition;
            $package->cout_vente = $request->cout_vente;
            $package->gain = $request->cout_vente - $request->cout_acquisition;
            $package->nb_products = $request->nb_products;
            $package->nb_jours = $request->nb_jours;
            $package->publie = $request->publie;
            $package->user_id = $request->user_id;
            $package->type_id = $request->type_id;
            $package->libelle = $request->libelle;
            $package->save();
            if ($package->wasChanged()) {
                return response()->json([
                    'data' => $package,
                    'status' => 'true'
                ]);
            }
            else{
                return response()->json([
                    'status' => 'false'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'L\'identifiant de l\'utilisateur ou celui du type de package est introuvable'
            ]);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $package = Package::find($id);
        $achat = Achat::all();
        if ($achat->contains('package_id', $id)) {
            return response()->json([
                'status' => 'not deleted. already used somewhere'
            ]);
        } else {
                $package->delete($id);
            if ($package->deleted_at != null) {
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
