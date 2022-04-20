<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\Package;
use App\Models\Rapport;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rapports = Rapport::with('achat')->get();
        if ($rapports != null) {
            return response()->json([
                'status' => 'true',
                'data' => $rapports
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'fetching error',
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $rapport = Rapport::with('achat')->find($id);
        $package = Package::find($rapport->achat->package_id);
        $rapport->package = $package;
        if ($rapport != null) {
            return response()->json([
                'status' => 'true',
                'data' => $rapport
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'data' => 'id not found'
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
        $rapport = Rapport::find($id);
        $rapport->delete($id);
            if ($rapport->deleted_at != null) {
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
