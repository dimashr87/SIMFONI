<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use JKD\SSO\Client\Provider\Keycloak;
use App\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Display login page.
     *
     * @return Renderable
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Handle login with standard credentials.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Handle SSO login process.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showLoginForm(Request $request)
    {
        $provider = $this->ssoInstance();

        if (!isset($_GET['code'])) {
            // Untuk mendapatkan authorization code
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;

        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        } else {
            try {
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
            } catch (\Exception $e) {
                exit('Gagal mendapatkan akses token: '.$e->getMessage());
            }

            try {
                $user = $provider->getResourceOwner($token);
                $email = $user->getEmail();
                $id = User::where('email', $email)->first();

                if (!empty($id)) {
                    $id = $id->id;
                } else {
                    $new_user = User::create([
                        'name' => $user->getName(),
                        'email' => $user->getEmail(),
                    ]);
                    $id = $new_user->id;
                }

                if (Auth::loginUsingId($id)) {
                    return redirect()->intended('/');
                } else {
                    return redirect('/');
                }
            } catch (\Exception $e) {
                exit('Gagal login: '.$e->getMessage());
            }
        }
    }

    /**
     * Logout user and redirect to appropriate logout URL.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Proses logout untuk pengguna SSO
            $user->sso_token = null;
            $provider = $this->ssoInstance();
            $user->save();
            $this->guard()->logout();
        } else {
            // Proses logout biasa
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user && $user->sso_token) {
            return redirect($provider->getLogoutUrl());
        }

        return redirect('/login');
    }

    /**
     * @return Keycloak
     *
     * Function to access Keycloak - BPS SSO
     */
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
