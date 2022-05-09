<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\Operation;
use App\Models\Rapport;
use App\Models\Vente;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\User;

class VenteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ventes = Vente::with('package')->get();
        
            foreach ($ventes as $key => $vente) {
                
                $souscripteurs = [];
                $vente->fournisseur = User::find($vente->package->user_id);
                $pivot = Package::with('sell')->find($vente->package->id);
                foreach ($pivot->sell as $key => $sell) {
                    $buyer = User::find($sell->user_id);
                    $buyer->pieces_achetees = $sell->nb_pieces;
                    array_push($souscripteurs, $buyer);
                }
                $vente->souscripteurs = $souscripteurs;
            }
            
        return response()->json([
            'status' => 'true',
            'data' => $ventes
        ]);
    }

    public function getReports($id)
    {
        $rapports = Rapport::with('achat')->where('vente_id', $id)->get();
        $rapports->each(function ($rapport){
            $rapport->package = Package::find($rapport->achat->package_id);
        });
        return response()->json([
            'status' => 'true',
            'data' => $rapports
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
        
        if ($request->has(['nb_ventes', 'package_id'])) {
            $packages = Achat::with('user')->with('package')->where('package_id',$request->package_id)->where('consommed', 0)->get();
            if (count($packages) == 0 ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Il n\'y a pas de souscription pour ce package',
                ]);
            }
            if ($packages->sum('nb_pieces') < intval($request->nb_ventes)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Le nombre de ventes entré est différent de l\'existant',
                ]);
            }  
            $cpte = 0;
            $decimal= 0;
            $venteTotal = 0;
            $ventes = new Vente();
            $ventes->nb_ventes = $request->nb_ventes;
            $ventes->cout_total = 0;
            $ventes->package_id = $request->package_id;
            $ventes->save();
            
            $total = $packages->sum('nb_pieces');
            foreach ($packages as $key => $achat) {
                $percent = ($achat->nb_pieces * 100) / $total;
                if (++$cpte == count($packages)) {
                    $nb_dist = $ventes->nb_ventes - $decimal;
                }
                else {
                    if (($percent * $ventes->nb_ventes) % 100 == 0) {
                        $nb_dist = ($percent * $ventes->nb_ventes) / 100;
                    } else {
                        $nb_dist = intval(($percent * $ventes->nb_ventes) / 100);
                    }
                    $decimal += $nb_dist;
                }
                $rapportVerif= Rapport::with('achat')->where('achat_id',$achat['id'])->get();
                
                if (count($rapportVerif) > 0) {
                    $totalRap = $rapportVerif->sum('cout');
                    if (($achat->package->cout_vente * $achat->nb_pieces) > $totalRap) {
                        if (($nb_dist*$achat->package->cout_vente + $totalRap) > ($achat->package->cout_vente * $achat->nb_pieces)) {
                            $rapport = new Rapport();
                            $rapport->produits_vendus = $nb_dist;
                            $rapport->cout = ($achat->package->cout_vente * $achat->nb_pieces) - $totalRap;
                            $rapport->vente_id = $ventes->id;
                            $rapport->achat_id = $achat['id'];
                            $rapport->save();
                            $user = User::find($achat->user_id);
                            $ope = new Operation();
                            $ope->user_id = $user->id;
                            $ope->type = 'depot';
                            $ope->amount = $rapport->cout;
                            $ope->initiateur_id = $achat->package->user_id;
                            $ope->save();
                            $venteTotal += $rapport->cout;

                            $achat['consommed'] = 1;
                            $achat->save();
                        }else{
                            $rapport = new Rapport();
                            $rapport->produits_vendus = $nb_dist;
                            $rapport->cout = ($nb_dist * $achat->package->cout_vente);
                            $rapport->vente_id = $ventes->id;
                            $rapport->achat_id = $achat['id'];
                            $rapport->save();
                            $user = User::find($achat->user_id);
                            $ope = new Operation();
                            $ope->user_id = $user->id;
                            $ope->type = 'depot';
                            $ope->amount = $rapport->cout;
                            $ope->initiateur_id = $achat->package->user_id;
                            $ope->save();
                            $venteTotal += $rapport->cout;
                        }
                    }else{
                        $achat['consommed'] = 1;
                        $achat->save();
                    }
                    
                    # code...
                }else{
                    $rapport = new Rapport();
                    $rapport->produits_vendus = $nb_dist;
                    $rapport->cout = ($nb_dist * $achat->package->cout_vente);
                    $rapport->vente_id = $ventes->id;
                    $rapport->achat_id = $achat['id'];
                    $rapport->save();
                    $user = User::find($achat->user_id);
                    $ope = new Operation();
                    $ope->user_id = $user->id;
                    $ope->type = 'depot';
                    $ope->amount = $rapport->cout;
                    $ope->initiateur_id = $achat->package->user_id;
                    $ope->save();
                    $venteTotal += $rapport->cout;
                }

                
            } 
            $results = Vente::with('package')->find($ventes->id);
            $results->cout_total = $venteTotal;
            /* $results->toJson(); */
            $results->save();
            return response()->json([
                'status' => 'true',
                'data' => $results,
            ]);
            
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Fill all fields',
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
