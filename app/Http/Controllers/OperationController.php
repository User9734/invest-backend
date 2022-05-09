<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class operationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $operations = Operation::all();
        foreach ($operations as $key => $operation) {
            $init = User::find($operation->initiateur_id);
            $operation->initiateur = $init;
        }
        return response()->json([
            'data' => $operations,
            'status' => 'true'
        ]);
    }

    public function getSellerOps($id)
    {
        $sellers = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('users.*')
            ->where('users.deleted_at', null)
            ->where('roles.libelle', 'fournisseur')
            ->get();
        if ($sellers->contains('id', $id)) {
            $operations = Operation::where('id', $id)->get();
            foreach ($operations as $key => $operation) {
                $init = User::find($operation->initiateur_id);
                $operation->initiateur = $init;
            }
            return response()->json([
                'data' => $operations,
                'status' => 'true'
            ]);
        } else {
            return response()->json([
                'status' => 'flase',
                'message' => 'user isn\'t a seller'
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
        $initiator = $request->user('api');
        $request->validate([
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'user_id' => 'required|numeric',
            'initiateur_id' => 'required|numeric',
        ]);
        $operation = Operation::create([
            'type' => $request->type,
            'amount' => $request->amount,
            'user_id' => $request->user_id,
            'initiateur_id' => $initiator->id,
        ]);
        $user = User::find($request->user_id);
        if ($user->id != null) {
            if ($request->has(['type', 'amount', 'user_id'])) {
                if ($request->type == 'retrait') {
                    if ($user->solde > $request->amount) {
                        $user->solde -= $request->amount;
                        $user->save();
                    }
                    else {
                        return response()->json([
                            'status' => 'false',
                            'message' => 'low balance'
                        ]);
                    }
                }
                else {
                    $user->solde += $request->amount;
                    $user->save();
                }
                return response()->json([
                    'data' => $operation,
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
                'message' => 'L\'identifiant de l\'utilisateur est introuvable'
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
        $operation = Operation::find($id);
        $init = User::find($operation->initiateur_id);
            $operation->initiateur = $init;
        if ($operation != null) {
            return response()->json([
                'data' => $operation,
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
        $operation = Operation::find($id);
        $operation->user_id = $request->user_id;
        $operation->amount = $request->amount;
        $operation->type = $request->type;
        $operation->initiateur_id = $request->initiateur_id;
        $operation->save();
        $user = User::find($request->user_id);
        if ($user != null) {
            if ($operation->wasChanged()) {
                return response()->json([
                    'data' => $operation,
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
                'message' => 'L\'identifiant de l\'utilisateur est introuvable'
            ]);
        }
        
    }
    /** 
    * @param  int  $id 
    * @return \Illuminate\Http\Response
    */
    public function getOps($id)
    {
        $operations = Operation::where('user_id', $id)
                                ->get();
        foreach ($operations as $key => $operation) {
            $init = User::find($operation->initiateur_id);
            $operation->initiateur = $init;
        }
        if ($operations !=null) {
            return response()->json([
                'data' => $operations,
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $operation = Operation::find($id);
        if ($operation->user_id != null) {
            return response()->json([
                'status' => 'not deleted. already used somewhere'
            ]);
        } else {
            $operation->delete($id);
        if ($operation->deleted_at != null) {
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
