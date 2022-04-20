<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\UserRole;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $role = null;
        $role = Role::all();
        if ($role != null) {
            return response()->json([
                'data' => $role,
                'status' => 'true'
            ]);
        } else {
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
        $role = Role::create($request->all());
        if ($request->has('libelle')) {
            return response()->json([
                'data' => $role,
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
        $role = Role::find($id);
        if ($role != null) {
            return response()->json([
                'data' => $role,
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
        $role = Role::find($id);
        $role->libelle = $request->libelle;
        $role->save();
        if ($role->wasChanged() == true) {
            return response()->json([
                'data' => $role,
                'status' => 'true'
            ]);
        } else {
            return response()->json([
                'status' => 'false'
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
        $role = Role::find($id);
        $test = UserRole::all();
        if ($test->contains('role_id', $role->id)) {
            return response()->json([
                'status' => 'not deleted. already used somewhere'
            ]);
        } else {
            $role->delete($id);
            if ($role->deleted_at != null) {
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
