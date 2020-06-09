<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth Routes
Route::post('/login', 'AuthController@login');
Route::post('/register', 'AuthController@register');

// User Routes
Route::get('/users', 'UserController@get_users');

Route::get('/user/{id}', 'UserController@get_user');
Route::put('/user/{id}', 'UserController@update_user');

Route::get('/user/contacts', 'UserController@get_contacts');
Route::get('/user/letters', 'UserController@get_letters');
Route::get('/user/org', 'UserController@get_org');
// Route::get('/user/stats', 'UserController@get_stats');

// Letter Routes
Route::get('/letters', 'LetterController@get_letters');

Route::get('/letter/{id}', 'LetterController@get_letter');
Route::post('/letter', 'LetterController@create_letter');

// Public Routes
Route::get('/public/letter/html/{verify_token}', 'PublicController@letter_html');

// Contact Routes
Route::get('/contacts', 'ContactController@get_contacts');
Route::get('/contact/{id}', 'ContactController@get_contact');
Route::post('/contact', 'ContactController@create_contact');
Route::put('/contact/{id}', 'ContactController@update_contact');

// Facility Routes
Route::get('/facilities', 'FacilityController@get_facilities');
Route::get('/facility/{id}', 'FacilityController@get_facility');
Route::post('/facility', 'FacilityController@create_facility');
Route::put('/facility/{id}', 'FacilityController@update_facility');

// Zip Routes
Route::get('/zips/{zip?}', 'ZipController@query_zips');

// Donor Routes
Route::post('/donate', 'DonateController@process_donate');
Route::get('/donation/cancel/{cancel_url}', 'DonateController@cancel');

// Organization Routes
Route::get("/orgs", "OrgController@get_orgs");
Route::get("/org/{id}", "OrgController@get_org");
Route::post("/org", "OrgController@create_org");
Route::put("/org/{id}", "OrgController@update_org");

// OrgUser Routes
Route::get("/org/users", "OrgUserController@get_org_users");
Route::get("/org/user/{id}", "OrgUserController@get_org_user");
Route::post("/org/user", "OrgUserController@create_org_user");
