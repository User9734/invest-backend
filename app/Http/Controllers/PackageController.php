<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\User;
use App\Models\Type;

use function PHPSTORM_META\map;
use function PHPUnit\Framework\isEmpty;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $packages = Package::with('sell')->with('type')->get();
        $packages->each(function ($package){
            $seller = User::find($package->user_id);
            $package->seller = $seller;
        });
        return response()->json([
            'data' => $packages,
            'status' => 'true'
        ]);
    }

    public function getPublished()
    {
        $packages = Package::with('type')->with('sell')->where('etat', 'publie')->get();
        $packages->each(function ($package){
            $seller = User::find($package->user_id);
            $package->seller = $seller;
        });
        return response()->json([
            'data' => $packages,
            'status' => 'true'
        ]);
    }

    public function getSubscribed($id)
    {
        $packages = Package::with('type')->has('sell')->where('user_id', $id)->get();
        return response()->json([
            'data' => $packages,
            'status' => 'true'
        ]);
    }

    public function getUnpublished()
    {
        $packages = Package::with('type')->with('sell')->where('etat', 'en cours de traitement')->get();
        $packages->each(function ($package){
            $seller = User::find($package->user_id);
            $package->seller = $seller;
        });
        return response()->json([
            'data' => $packages,
            'status' => 'true'
        ]);
    }

    public function getRejected()
    {
        $packages = Package::with('type')->with('sell')->where('etat', 'rejete')->get();
        $packages->each(function ($package){
            $seller = User::find($package->user_id);
            $package->seller = $seller;
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
            ->where('users.deleted_at', null)
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
                        $package->gain_par_piece = intval($request->cout_vente) - intval($request->cout_acquisition);
                        $package->nb_products = $request->nb_products;
                        $package->nb_jours = $request->nb_jours;
                        $package->user_id = $request->user_id;
                        $package->type_id = $request->type_id;
                        $package->libelle = $request->libelle;
                        $package->pieces_restantes = $request->nb_products;
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
        $package = Package::with('sell')->with('user')->where('id', $id)->first();
        if ($package != null) {
            $type = Type::find($package->type_id);
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
        
            $package = Package::find($id);
            if (!isEmpty($package->sell) || $package->etat == 'publie') {
                return response()->json([
                    'status' => 'false',
                    'message' => 'article déja acheté ou déja publié'
                ]);
            } else {
                $package->etat = $request->etat;
                $package->cout_acquisition = $request->cout_acquisition;
                        $package->cout_vente = $request->cout_vente;
                        $package->nb_products = $request->nb_products;
                        $package->nb_jours = $request->nb_jours;
                        $package->user_id = $request->user_id;
                        $package->type_id = $request->type_id;
                        $package->libelle = $request->libelle;
                $package->commentaire_rejet = $request->commentaire;
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
            }
            
        
    }

    public function edit(Request $request, $id)
    {
        
            $package = Package::with('sell')->find($id);
            if (!isEmpty($package->sell)) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'article déja acheté'
                ]);
            } else {
                $package->etat = $request->etat;
                $package->commentaire_rejet = $request->commentaire;
                $package->save();
                if ($package->wasChanged()) {
                    return response()->json([
                        'data' => $package,
                        'status' => 'true'
                    ]);
                }
                else{
                    return response()->json([
                        'status' => 'false',
                        'message' => 'not edited'
                    ]);
                }
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
