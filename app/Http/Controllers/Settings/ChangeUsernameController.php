<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Profile;
use App\User;
use Auth;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChangeUsernameController extends Controller
{
    // Quantos dias o usuário deve esperar entre trocas
    const COOLDOWN_DAYS = 3;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Exibe o formulário de troca de username.
     * Renderiza dentro da página de configurações.
     */
    public function show()
    {
        $user = Auth::user();
        $lastChange = Cache::get('username:last_change:' . $user->id);
        $canChange = ! $lastChange || now()->diffInDays($lastChange) >= self::COOLDOWN_DAYS;
        $daysLeft = $lastChange
            ? max(0, self::COOLDOWN_DAYS - now()->diffInDays($lastChange))
            : 0;

        return view('settings.username', compact('user', 'canChange', 'daysLeft'));
    }

    /**
     * Processa a troca de username.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Verifica cooldown
        $lastChange = Cache::get('username:last_change:' . $user->id);
        if ($lastChange && now()->diffInDays($lastChange) < self::COOLDOWN_DAYS) {
            $daysLeft = self::COOLDOWN_DAYS - now()->diffInDays($lastChange);
            return back()->withErrors([
                'username' => "Você só pode trocar o username a cada " . self::COOLDOWN_DAYS . " dias. Aguarde {$daysLeft} dia(s)."
            ]);
        }

        $request->validate([
            'username' => [
                'required',
                'string',
                'min:2',
                'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',  // só letras, números e _
            ],
        ], [
            'username.regex' => 'O username só pode conter letras, números e underscores (_).',
            'username.min'   => 'O username deve ter pelo menos 2 caracteres.',
            'username.max'   => 'O username não pode ter mais de 30 caracteres.',
        ]);

        $newUsername = strtolower($request->input('username'));
        $oldUsername = $user->username;

        // Não mudou nada
        if ($newUsername === $oldUsername) {
            return back()->withErrors(['username' => 'O novo username é igual ao atual.']);
        }

        // Verifica se está na lista de bloqueados (reservados/vendidos)
        $isReserved = DB::table('reserved_usernames')
            ->where('username', $newUsername)
            ->exists();

        if ($isReserved) {
            return back()->withErrors(['username' => 'Este username não está disponível.']);
        }

        // Verifica se já está em uso por outro usuário
        $taken = User::where('username', $newUsername)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($taken) {
            return back()->withErrors(['username' => 'Este username já está em uso.']);
        }

        // Verifica também na tabela de profiles (federação)
        $takenProfile = Profile::where('username', $newUsername)
            ->where('user_id', '!=', $user->id)
            ->whereNull('domain')  // apenas perfis locais
            ->exists();

        if ($takenProfile) {
            return back()->withErrors(['username' => 'Este username já está em uso.']);
        }

        // Aplica a troca dentro de uma transação
        DB::transaction(function () use ($user, $oldUsername, $newUsername) {
            // Atualiza na tabela users
            User::where('id', $user->id)->update(['username' => $newUsername]);

            // Atualiza na tabela profiles
            Profile::where('user_id', $user->id)->update(['username' => $newUsername]);

            // Limpa caches relacionados ao perfil antigo e novo
            Cache::forget('pfc:cached-user:wot:' . strtolower($oldUsername));
            Cache::forget('pfc:cached-user:wt:'  . strtolower($oldUsername));
            Cache::forget('pfc:cached-user:wot:' . strtolower($newUsername));
            Cache::forget('pfc:cached-user:wt:'  . strtolower($newUsername));
            Cache::forget('profile:following_count:' . $user->profile_id);
            Cache::forget('profile:follower_count:'  . $user->profile_id);
            Cache::forget('avatar:' . $user->profile_id);
        });

        // Registra o cooldown
        Cache::put('username:last_change:' . $user->id, now(), now()->addDays(self::COOLDOWN_DAYS + 5));

        return redirect('/settings/username')
            ->with('success', "Username alterado para @{$newUsername} com sucesso!");
    }
}
