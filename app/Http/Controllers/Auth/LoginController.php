<?php

namespace App\Http\Controllers\Auth;

use App\AccountLog;
use App\Http\Controllers\Controller;
use App\Services\BouncerService;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\LinkedAccountController;
use App\Models\LinkedAccount;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/i/web';

    protected $maxAttempts = 5;

    protected $decayMinutes = 60;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    public function username()
    {
    return 'username';
    }

    protected function credentials(Request $request)
    {
        $login = $request->input('username');
 
        // Detecta se é email ou username
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
 
    return [
        $field     => $login,
        'password' => $request->input('password'),
        ];
    }
 

    public function showLoginForm()
    {
        if (config('pixelfed.bouncer.cloud_ips.ban_logins')) {
            abort_if(BouncerService::checkIp(request()->ip()), 404);
        }

        return view('auth.login');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function validateLogin($request)
    {
        if (config('pixelfed.bouncer.cloud_ips.ban_logins')) {
            abort_if(BouncerService::checkIp($request->ip()), 404);
        }

        $rules = [
            $this->username() => 'required|email',
            'password' => 'required|string|min:6',
        ];
        $messages = [];

        if (
            (bool) config_cache('captcha.enabled') &&
            (bool) config_cache('captcha.active.login') ||
            (
                (bool) config_cache('captcha.triggers.login.enabled') &&
                request()->session()->has('login_attempts') &&
                request()->session()->get('login_attempts') >= config('captcha.triggers.login.attempts')
            )
        ) {
            $rules['h-captcha-response'] = 'required|filled|captcha|min:5';
            $messages['h-captcha-response.required'] = 'The captcha must be filled';
        }
        $request->validate($rules, $messages);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Recupera o ID do usuário que estava logado antes (guardado na sessão)
        $previousUserId = $request->session()->get('rpgram_previous_user_id');
 
        if ($previousUserId && $previousUserId !== $user->id) {
            $previousUser = \App\User::find($previousUserId);
            if ($previousUser) {
                LinkedAccountController::linkAfterLogin($previousUser, $user);
            }
        }
 
        // Guarda o usuário atual na sessão para o próximo login
        $request->session()->put('rpgram_previous_user_id', $user->id);
 
        return redirect()->intended($this->redirectPath());
    }

        protected function loggedOut(Request $request)
    {
        // A sessão já foi invalidada pelo logout padrão do Laravel,
    }

        public function logout(Request $request)
    {
        $userId = Auth::id();
 
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 
        // Guarda na nova sessão quem estava logado (para vincular após próximo login)
        if ($request->has('linking')) {
            $request->session()->put('rpgram_previous_user_id', $userId);
        }
 
        return redirect('/login');
    }
    
    /**
     * Get the failed login response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        if (config('captcha.triggers.login.enabled')) {
            if ($request->session()->has('login_attempts')) {
                $ct = $request->session()->get('login_attempts');
                $request->session()->put('login_attempts', $ct + 1);
            } else {
                $request->session()->put('login_attempts', 1);
            }
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}
