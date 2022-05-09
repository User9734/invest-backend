<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Achat;
use App\Models\Operation;
use App\Models\Type;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class AchatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $achats = Achat::with('user')->with('package')->with('rapport')->get();
        
        /* $achats->each(function ($achat, $id){
            $package = Package::where('id', $achat->package_id)
                                ->get();
        $achat->package = $package;
        }); */
        return response()->json([
            'status' => 'true',
            'data' => $achats
        ]);
    }

    public function getForUser($id)
    {
        $user = User::find($id);
        if ($user != null) {
            $visibles = Achat::where('user_id', $user->id)->with('package')->with('rapport')->get();
            $visibles->each(function ($visible){
                $type = Type::find($visible->package->type_id);
                $visible->package->type = $type;
                $visible->rapport->cout_total = $visible->rapport->sum('cout');
            });
            return response()->json([
                'status' => 'true',
                'data' => $visibles
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'user not found'
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
        
        if ($request->has(['package_id','user_id', 'nb_products'])) {
            $quotas = 0;
        $buyers = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('users.*')
            ->where('roles.libelle', 'investisseur')
            ->get();
        $users = User::all();
        $packages = Package::all();
        $user = User::find($request->user_id);
        
        $package = Package::find($request->package_id);
        $vendor = User::find($package->user_id);
        if ($package->has('sell')) {
            $package->sell->each(function($element){
                global $quotas;
                $quotas += $element->nb_products;
            });
        } else {
            global $quotas;
            $quotas = $request->nb_products;
        }
        
                $request->validate([
                    'package_id' => 'required|integer',
                    'user_id' => 'required|integer',
                    'nb_products' => 'required|integer|between:'. $package->nb_products / 5 .','. $package->nb_products ,
                ]);
            if ($users->contains('id', $request->user_id) == false || $packages->contains('id', $request->package_id) == false) {
                return response()->json([
                    'status' => '404',
                    'message' => 'user id or package id not found.'
                ]);
            } else {
                if ($buyers->contains('id', $request->user_id) == true) {
                    if ($package->nb_products <= $quotas) {
                        return response()->json([
                            'status' => 'false',
                            'message' => 'package already has subscription.'
                        ], 700);
                    } else {
                        if ($user->solde >= ($package->cout_acquisition * $package->nb_products)) {
                            $stamp = time() + ($package->nb_jours * 24 * 60 * 60);
                            $achat = new Achat();
                            $achat->user_id = $request->user_id;
                            $achat->package_id = $request->package_id;
                            $achat->nb_pieces = $request->nb_products;
                            $achat->date_validite = date('Y-m-d', $stamp);
                            $achat->save();
                            $ope = new Operation();
                            $ope->user_id = $user->id;
                            $ope->type = 'retrait';
                            $ope->amount = ($package->cout_acquisition * $request->nb_products);
                            $ope->initiateur_id = $user->id;
                            $ope->save();
                            $ope = new Operation();
                            $ope->user_id = $vendor->id;
                            $ope->type = 'depot';
                            $ope->amount = ($package->cout_acquisition * $request->nb_products);
                            $ope->initiateur_id = $user->id;
                            $ope->save();
                            $package->pieces_restantes = $package->pieces_restantes - $request->nb_products;
                            $package->save();
                            return response()->json([
                                'data' => $package,
                                'status' => 'true'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 'false',
                                'message' => 'balance below package price.'
                            ]);
                        }
                    }
                    
                    
                } else {
                    return response()->json([
                        'status' => 'false',
                        'message' => 'this user isn\'t an investor.'
                    ]);
                }
                
            }
        }
        else{
            return response()->json([
                'status' => 'false',
                'message' => 'fill all fields.'
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
        $achat = Achat::where('id', $id)->with('package')->with('rapport')->get();
            $achat->each(function ($act){
                $type = Type::find($act->package->type_id);
                $act->package->type = $type;
            });
            if ($achat != null) {
                return response()->json([
                    'data' => $achat,
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
        $achat = Achat::find($id);
        $package = Package::find($achat->package_id);
        $user = User::find($achat->user_id);
        $request->validate([
            'validation' => 'required|boolean',
        ]);
        if ($user != null && $package != null) {
            if ($request->has(['validation'])) {
                $achat->validation = intval($request->validation);
                $achat->save();
                return response()->json([
                    'status' => 'true',
                    'data' => $achat
                ]);
            }
            else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'fill all fields!'
                ]);
            }
        } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'id introuvable'
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
        //
    }
}
