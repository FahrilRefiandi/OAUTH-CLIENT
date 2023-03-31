<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\OauthToken;
use Illuminate\Support\Facades\Http;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $response = OauthToken::where('user_id', Auth::user()->id)->first();

        // $user = Http::withToken("eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI5OGQyMjMzMy0xMjljLTRlZmUtOTJlMi01ZWFkYzlkMzM2NzMiLCJqdGkiOiI3ZDM3YmYzODBiMWU2NmNiOWE1MzczZTZmNWZjYzc1ZGNjYWY0Yjc2OGYwMjhmMWU3NDQ1NmIwZGFiM2Q2MjExZTM2ODM1MDEyZDYxYTc3NyIsImlhdCI6MTY4MDI4Njg0OS43MTg2MzMsIm5iZiI6MTY4MDI4Njg0OS43MTg2MzYsImV4cCI6MTcxMTkwOTI0OS42NjQwMDMsInN1YiI6IjIiLCJzY29wZXMiOltdfQ.UPMJI2RZ0GoTdEJwQSwUSm-2t11IJ0U1TL0hQo2cflDCKWLDge7noYEYX2DcJezVQswGYfvbtKaKG7zLfwC64f4b-ndGLJoLA4ihqAoj8CgQVqFT5TZ6U5Z0Ml7ICIXgpuQjLt_ahI_tGdfwGYEDJl--vA66ZywgpH78_z6Hl0XXDQJ6On3fI_BxnLHWDw_RHHN8z3lDdPzjMFYmZzlUgxYJfVtpiwTuWMdr5bMO9JNUb48wx2pK_rGSKBZTLeLEC3uXxF0geXamm2MIj545zdCz8oeOvEbe2_ZHV4pxvYTRzryv3xYl_dcX7I4NBaiY5VfxKbrcUsYtl5KSd5AKTQe7766TNAdw9AJhSIkFn8kCzM8OH3unXONnBV-kpL1T8_3Np1LW_SevNd8zxf2Al1xdFGHMb1tLsK8k-QXMiH8RTuBjsenSlFcKeZxkf-Cx2pwupR4-VfaKEazH4u1dZ3Jhug6sebQ5dopftQpjpdjpPfn7m_-YawvRVQbBoSln7tV1m169TBQo9kFMqKo1zE2UbRG0AbkotZ1bjLzpjke-rOV1Y6Aw0cSs4QtH63bqU2yaa1gmODUbKl6oM92GHQ6fJKlIPOqtKTaq57_YCLToXfT15JRmViKPz6bbEa831iZXB9WDsBX--5TCz62XOyzYkVFBFCmwDQpV-noah_c")->get(env('OAUTH_HOST_SERVER') . '/api/logout')->json();

        // $user = Http::withToken($response->access_token)
        //     ->get(env('OAUTH_HOST_SERVER') . '/api/logout')->json();
        

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
