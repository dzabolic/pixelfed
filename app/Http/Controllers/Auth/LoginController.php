<?php

namespace App\Http\Controllers\Auth;

use App\AccountLog;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LinkedAccountController;
use App\Models\LinkedAccount;
use App\Services\BouncerService;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/i/web';
    protected $maxAttempts = 5;
    protected $decayMinutes = 60;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * O campo de login aceita tanto 'login' (nome do campo no HTML)
     * quanto 'username'. Retornamos 'login' para o trait saber qual campo pegar.
     */
    public function username()
    {
        return 'login';
    }

    /**
     * Sobrescreve as credenciais para buscar por username OU email,
     * independente do que o usuário digitou no campo 'login'.
     */
    protected function credentials(Request $request)
    {
        $loginValue = $request->input('login');

        // Detecta se é email ou username
        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field     => $loginValue,
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
     * Validação: exige apenas 'login' e 'password'.
     */
    public function validateLogin($request)
    {
        if (config('pixelfed.bouncer.cloud_ips.ban_logins')) {
            abort_if(BouncerService::checkIp($request->ip()), 404);
        }

        $rules = [
            'login'    => 'required|string',
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
     * Após login: vincula contas se houver sessão anterior (troca de conta).
     */
    protected function authenticated(Request $request, $user)
    {
        $previousUserId = $request->session()->get('rpgram_previous_user_id');

        if ($previousUserId && $previousUserId !== $user->id) {
            $previousUser = User::find($previousUserId);
            if ($previousUser) {
                LinkedAccountController::linkAfterLogin($previousUser, $user);
            }
        }

        $request->session()->put('rpgram_previous_user_id', $user->id);

        return redirect()->intended($this->redirectPath());
    }

    protected function loggedOut(Request $request)
    {
        // Sessão já foi invalidada pelo logout padrão do Laravel
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();

        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->has('linking')) {
            $request->session()->put('rpgram_previous_user_id', $userId);
        }

        return redirect('/login');
    }

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
