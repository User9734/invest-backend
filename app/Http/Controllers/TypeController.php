<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = Type::all();
        return response()->json([
            'data' => $types,
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
        $type = new Type();
        $type->libelle = $request->libelle; 
        if (request()->hasFile('photo')) {
            $file = $request->file('photo');
                $fileName= $file->getClientOriginalName();
                $file->move(public_path('/images'),$fileName);

                $image_path = "/images/" . $fileName;
        }
        $type->photo = $image_path;
        $type->save();
        if ($type) {
            return response()->json([
                'data' => $type,
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $type = Type::find($id);
        if ($type != null) {
            return response()->json([
                'data' => $type,
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
        $type = Type::find($id);
        $type->libelle = $request->libelle;
        $type->save();
        if ($type->wasChanged()) {
            return response()->json([
                'data' => $type,
                'status' => 'true'
            ]);
        }
        else{
            return response()->json([
                'status' => 'false'
            ]);
        }
    }

    public function getPopulars(){
        $packages = Package::with('type')->get();

        $counted = $packages->countBy(function ($package){
            if ($package->type != null) {
                return $package->type->libelle;
            }
        }); 
        return response()->json([
            'status' => 'true',
            'data' => $counted
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
        $type = Type::find($id);
        $package = Package::all();
        if ($package->contains('type_id', $id)) {
            return response()->json([
                'status' => 'true',
                'data' => 'not deleted. package type already used.'
            ]);
        } else {
            $type->delete($id);
            if ($type->deleted_at != null) {
                return response()->json([
                    'status' => 'deleted'
                ]);
            }
            else {
                return response()->json([
                    'status' => 'not deleted'
                ]);
            }           
        }
        
        
    }
}
