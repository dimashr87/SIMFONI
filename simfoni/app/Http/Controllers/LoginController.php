<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use JKD\SSO\Client\Provider\Keycloak;
use App\Models\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Ambil data user yang sedang login
            $user = Auth::user();

            // Simpan niplama ke dalam session
            $request->session()->put('niplama', $user->niplama);

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function showLoginForm(Request $request)
    {
        $provider = $this->ssoInstance();

        if (!isset($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl();
            session(['oauth2state' => $provider->getState()]);
            return redirect($authUrl);
        } elseif (empty($_GET['state']) || ($_GET['state'] !== session('oauth2state'))) {
            session()->forget('oauth2state');
            return abort(403, 'Invalid state');
        } else {
            try {
                // Get access token
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                // Save token in the session if needed
                session(['sso_token' => $token->getToken()]);
            } catch (\Exception $e) {
                return abort(500, 'Failed to get access token: ' . $e->getMessage());
            }

            try {
                // Fetch user info from the token
                $userResource = $provider->getResourceOwner($token);
                $email = $userResource->getEmail();
                $niplama = $userResource->getNip();
                $level = 3;
                // Find user by email or create a new one
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'username' => $userResource->getUsername(),
                        'password' => Hash::make('online'),
                        'level' => $level,
                        'firstname' => $userResource->getName() // Set default password to "online"
                        // Tambahkan kolom lain yang mungkin diperlukan
                        // Contoh: 'name' => $userResource->getName(),
                    ]
                );

                // Save the SSO token to the user
                $user->sso_token = $token->getToken();
                $user->save();
                $request->session()->regenerate();
                $request->session()->put('niplama', $niplama);
                // Log in the user
                if (Auth::loginUsingId($user->id)) {
                    return redirect()->intended($this->redirectTo);
                } else {
                    return redirect('/');
                }
            } catch (\Exception $e) {
                return abort(500, 'Login failed: ' . $e->getMessage());
            }
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->sso_token) {
            $provider = $this->ssoInstance();
            $user->sso_token = null;
            $user->save();

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $logoutUrl = $provider->getLogoutUrl([
                'post_logout_redirect_uri' => url('/login')
            ]);
            return redirect($logoutUrl);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function ssoInstance()
    {
        return new Keycloak([
            'authServerUrl'         => 'https://sso.bps.go.id',
            'realm'                 => 'pegawai-bps',
            'clientId'              => config('services.ssobps.id'),
            'clientSecret'          => config('services.ssobps.secret'),
            'redirectUri'           => config('services.ssobps.uri')
        ]);
    }
}
