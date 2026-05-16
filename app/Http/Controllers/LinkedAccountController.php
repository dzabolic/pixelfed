<?php

namespace App\Http\Controllers;

use App\Models\LinkedAccount;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkedAccountController extends Controller
{
    const MAX_LINKED = 50;

    /**
     * Retorna todas as contas vinculadas ao usuário atual.
     * Usado para popular a lista no menu de três tracinhos.
     */
    public function index()
    {
        $userId = Auth::id();

        // Busca todos os vínculos onde o usuário atual é owner OU linked
        $linked = LinkedAccount::where('owner_user_id', $userId)
            ->orWhere('linked_user_id', $userId)
            ->with(['owner.profile', 'linked.profile'])
            ->get()
            ->map(function ($la) use ($userId) {
                // Retorna a "outra" conta, não a atual
                $other = $la->owner_user_id === $userId ? $la->linked : $la->owner;
                return [
                    'id'           => $other->id,
                    'username'     => $other->username,
                    'avatar'       => $other->profile->avatarUrl(),
                    'switch_token' => $la->switch_token,
                ];
            })
            ->unique('id')
            ->take(self::MAX_LINKED)
            ->values();

        return response()->json($linked);
    }

    /**
     * Vincula a conta atual à conta do usuário que acabou de fazer login.
     * Chamado automaticamente após cada login bem-sucedido.
     */
    public static function linkAfterLogin(User $previousUser, User $newUser)
    {
        if ($previousUser->id === $newUser->id) return;

        $count = LinkedAccount::where('owner_user_id', $previousUser->id)
            ->orWhere('linked_user_id', $previousUser->id)
            ->count();

        if ($count >= self::MAX_LINKED) return;

        LinkedAccount::firstOrCreate(
            [
                'owner_user_id' => $previousUser->id,
                'linked_user_id' => $newUser->id,
            ],
            [
                'switch_token' => Str::random(64),
            ]
        );
    }

    /**
     * Troca de conta usando o switch_token.
     * Não exige senha — o token já prova que a conta foi vinculada antes.
     */
    public function switchAccount(Request $request, $token)
    {
        $currentUserId = Auth::id();

        $link = LinkedAccount::where('switch_token', $token)
            ->where(function ($q) use ($currentUserId) {
                $q->where('owner_user_id', $currentUserId)
                  ->orWhere('linked_user_id', $currentUserId);
            })
            ->firstOrFail();

        // Determina qual é a "outra" conta
        $targetUserId = $link->owner_user_id === $currentUserId
            ? $link->linked_user_id
            : $link->owner_user_id;

        $targetUser = User::findOrFail($targetUserId);

        // Faz o logout da conta atual e login na nova
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::login($targetUser);
        $request->session()->regenerate();

        return redirect('/' . $targetUser->username);
    }

    /**
     * Remove um vínculo (usuário remove uma conta da lista).
     */
    public function unlink(Request $request, $linkedUserId)
    {
        $currentUserId = Auth::id();

        LinkedAccount::where(function ($q) use ($currentUserId, $linkedUserId) {
            $q->where('owner_user_id', $currentUserId)
              ->where('linked_user_id', $linkedUserId);
        })->orWhere(function ($q) use ($currentUserId, $linkedUserId) {
            $q->where('owner_user_id', $linkedUserId)
              ->where('linked_user_id', $currentUserId);
        })->delete();

        return response()->json(['success' => true]);
    }
}
