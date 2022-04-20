<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\AchatController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\RoleuserController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group([    
    'namespace' => 'Auth',    
    'middleware' => 'api',    
    'prefix' => 'password'
], function () {    
    Route::post('create',[PasswordResetController::class, 'create']);
    Route::get('find/{token}', [PasswordResetController::class, 'find']);
    Route::post('reset', [PasswordResetController::class, 'reset']);
});

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('loginByAdmin', [AuthController::class, 'loginByAdmin']);
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('signupMobile', [AuthController::class, 'signupMobile']);
    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});



Route::apiResource('/users', UserController::class);
Route::get('/fournisseurs', [UserController::class, 'getSellers'])->middleware('auth:api');
Route::get('/investisseurs', [UserController::class, 'getInvestors'])->middleware('auth:api');
Route::get('/gains/{id}', [UserController::class, 'getGain'])->middleware('auth:api');
Route::get('/gains_inv/{id}', [UserController::class, 'getGainInv'])->middleware('auth:api');
Route::put('/profile/{id}', [UserController::class, 'profile'])->middleware('auth:api');

Route::apiResource('/rapports', RapportController::class)->middleware('auth:api');
Route::get('/rapport/{id}', [RapportController::class, 'getUsrRapport'])->middleware('auth:api');

Route::apiResource('/ventes', VenteController::class);
Route::get('/sells/{id}', [VenteController::class, 'getReports'])->middleware('auth:api');

Route::apiResource('/achats', AchatController::class)->middleware('auth:api');
Route::get('/user_achats/{id}', [AchatController::class, 'getForUser'])->middleware('auth:api');

Route::apiResource('/roles', RoleController::class);

Route::apiResource('/operations', OperationController::class)->middleware('auth:api');
Route::get('/opes/{id}', [OperationController::class, 'getOps'])->middleware('auth:api');

Route::apiResource('/types', TypeController::class);
Route::get('/populars', [TypeController::class, 'getPopulars'])->middleware('auth:api');

Route::apiResource('/packages', PackageController::class)->middleware('auth:api');
Route::get('/publies', [PackageController::class, 'getPublished']);
Route::get('/fourn_package/{id}', [PackageController::class, 'getSellerPackages'])->middleware('auth:api');

Route::apiResource('/user_roles', RoleuserController::class);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
