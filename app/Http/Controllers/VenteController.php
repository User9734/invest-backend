<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use App\Models\Vente;
use Illuminate\Http\Request;
use App\Models\Package;

class VenteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$ventes = Vente::all();
        $ventes = Vente::all();
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
        if ($request->has(['nb_ventes', 'type_id'])) {
            $packages = Package::where('type_id',$request->type_id)->has('user')->get();
            if (count($packages) == 0 ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Il n\'y a pas de souscription pour de ce type de package',
                ]);
            }
            $cpte = 0;
            $decimal = 0;
            $venteTotal = 0;
            $ventes = new Vente();
            $ventes->nb_ventes = $request->nb_ventes;
            $ventes->type_id = $request->type_id;
            $ventes->save();
            
            $total = $packages->sum('nb_products');
            foreach ($packages as $key => $package) {
                $percent = ($package->nb_products * 100) / $total;
                if (++$cpte == count($packages)) {
                    $nb_dist = $ventes->nb_ventes - $decimal;
                }
                else {
                    if (($percent * $ventes->nb_ventes) % 100 == 0) {
                        $nb_dist = ($percent * $ventes->nb_ventes) / 100;
                    } else {
                        $nb_dist = intval(($percent * $ventes->nb_ventes) / 100);
                        $decimal += $nb_dist;
                    }
                }
                // $coutRapport = ($percent * $request->nb_ventes) / 100;
                $rapportVerif= Rapport::where('achat_id',$package['user'][0]['pivot']['id'])->get();
                if (count($rapportVerif) > 0) {
                    $totalRap = $rapportVerif->sum('cout');
                    if (($package->cout_vente * $package->nb_products) > $totalRap) {
                        if (($nb_dist*$package->cout_vente + $totalRap) > ($package->cout_vente * $package->nb_products)) {
                            $rapport = new Rapport();
                            $rapport->produits_vendus = $nb_dist;
                            $rapport->cout = ($package->cout_vente * $package->nb_products) - $totalRap ;
                            $rapport->vente_id = $ventes->id;
                            $rapport->achat_id = $package['user'][0]['pivot']['id'];
                            $rapport->save();
                            $venteTotal += $rapport->cout;

                            $package['user'][0]['pivot']['consommed'] = 1;
                            $package['user'][0]['pivot']->save();
                        }else{
                            $rapport = new Rapport();
                            $rapport->produits_vendus = $nb_dist;
                            $rapport->cout = ($nb_dist * $package->cout_vente);
                            $rapport->vente_id = $ventes->id;
                            $rapport->achat_id = $package['user'][0]['pivot']['id'];
                            $rapport->save();
                            $venteTotal += $rapport->cout;
                        }
                    }else{
                        $package['user'][0]['pivot']['consommed'] = 1;
                        $package['user'][0]['pivot']->save();
                    }
                    
                    # code...
                }else{
                    $rapport = new Rapport();
                    $rapport->produits_vendus = $nb_dist;
                    $rapport->cout = ($nb_dist * $package->cout_vente);
                    $rapport->vente_id = $ventes->id;
                    $rapport->achat_id = $package['user'][0]['pivot']['id'];
                    $rapport->save();
                    $venteTotal += $rapport->cout;
                }

                
            } 

            $ventes->cout_total = $venteTotal;
            $ventes->save();
            return response()->json([
                'status' => 'true',
                'data' => $ventes,
            ]);
            
        } else {
            
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
