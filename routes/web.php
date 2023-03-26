<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::get('/redirect', function (Request $request) {
    $request->session()->put('state', $state = Str::random(40));

    $query = http_build_query([
        'client_id' => env('OAUTH_CLIENT_ID'),
        'redirect_uri' => env('OAUTH_REDIRECT_URI'),
        'response_type' => 'code',
        'scope' => '',
        'state' => $state,
        // 'prompt' => 'consent', // "none", "consent", or "login"
    ]);

    return redirect(env('OAUTH_HOST_SERVER').'/oauth/authorize?'.$query);
});


Route::get('/callback', function (Request $request) {
    $state = $request->session()->pull('state');


    $response = Http::asForm()->post(env('OAUTH_HOST_SERVER').'/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => env('OAUTH_CLIENT_ID'),
        'client_secret' => env('OAUTH_CLIENT_SECRET'),
        'redirect_uri' => env('OAUTH_REDIRECT_URI'),
        'code' => $request->code,
    ]);

    // return $response->json();

    if($response->failed()){
        return redirect('/login')->with('status', 'Login failed , permission denied');
    }


    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class,
        'Invalid state value.'
    );


    $response = $response->json();
    $user = Http::withToken($response['access_token'])->get(env('OAUTH_HOST_SERVER').'/api/user')->json();


    $user = App\Models\User::updateOrCreate([
        'email' => $user['email'],
    ],[
        'name' => $user['name'],
        'email' => $user['email'],
        'avatar' => $user['avatar'],
    ]);

    $user->token()->updateOrCreate([
        'user_id' => $user->id,
    ],[
        'access_token' => $response['access_token'],
        'refresh_token' => $response['refresh_token'],
        'expires_in' => $response['expires_in'],
    ]);

    Auth::login($user);

    return redirect('/dashboard');




});

require __DIR__ . '/auth.php';
