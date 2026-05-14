@extends('layouts.app')

@section('content')
<div id="rpgram-custom-profile" style="background-color: #000; color: #fff; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
    
    <!-- Cabeçalho: Avatar e Estatísticas -->
    <header style="padding: 16px; display: flex; flex-direction: column;">
        <div style="display: flex; align-items: center; margin-bottom: 12px;">
            <div style="margin-right: 28px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; border: 1px solid #333; padding: 2px;">
                    <img src="{{ $profile->avatarUrl() }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                </div>
            </div>
            <div style="display: flex; flex-grow: 1; justify-content: space-around; text-align: center;">
                <div><strong style="display: block; font-size: 15px;">{{ $profile->statuses_count ?? 0 }}</strong> <span style="font-size: 12px; color: #a8a8a8;">posts</span></div>
                <div><strong style="display: block; font-size: 15px;">{{ $profile->followers_count ?? 0 }}</strong> <span style="font-size: 12px; color: #a8a8a8;">seguidores</span></div>
                <div><strong style="display: block; font-size: 15px;">{{ $profile->following_count ?? 0 }}</strong> <span style="font-size: 12px; color: #a8a8a8;">seguindo</span></div>
            </div>
        </div>

        <!-- Nome em Negrito ACIMA da Bio -->
        <div style="font-size: 13px; line-height: 17px; margin-bottom: 16px;">
            <div style="font-weight: 700; font-size: 14px; margin-bottom: 2px;">{{ $profile->display_name }}</div>
            <div style="white-space: pre-wrap; color: #efefef;">{!! $profile->bio !!}</div>
        </div>

        <!-- Botões de Ação -->
        <div style="display: flex; gap: 8px; margin-bottom: 16px;">
            @if(Auth::check() && Auth::id() == $profile->user_id)
                <a href="{{ route('settings') }}" style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 6px 0; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Editar perfil</a>
                <a href="#" style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 6px 0; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Compartilhar perfil</a>
            @endif
        </div>
    </header>

    <!-- Destaques com Link de Criação Corrigido -->
    <div class="highlights-row" style="display: flex; overflow-x: auto; padding: 0 16px 16px; gap: 14px; scrollbar-width: none;">
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <div style="flex: 0 0 auto; text-align: center; width: 68px;">
                <a href="/i/web/profile/highlights/create" style="text-decoration: none; color: #fff;">
                    <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; display: flex; align-items: center; justify-content: center; margin-bottom: 5px;">
                        <span style="font-size: 26px; font-weight: 200;">+</span>
                    </div>
                    <span style="font-size: 11px;">Novo</span>
                </a>
            </div>
        @endif
        
        @if(isset($highlights))
            @foreach($highlights as $h)
                <div style="flex: 0 0 auto; text-align: center; width: 68px;">
                    <a href="/p/highlights/{{ $h->id }}" style="text-decoration: none; color: #fff;">
                        <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; padding: 2px; margin-bottom: 5px;">
                            <img src="{{ $h->cover_path }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        </div>
                        <span style="font-size: 11px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $h->title }}</span>
                    </a>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Abas de Navegação -->
    <div style="display: flex; justify-content: space-around; border-top: 1px solid #262626; padding: 12px 0;">
        <a href="?tab=posts" style="text-decoration: none;">
            <i class="fas fa-th" style="color: {{ !request('tab') || request('tab') == 'posts' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i>
        </a>
        <a href="?tab=reposts" style="text-decoration: none;">
            <i class="fas fa-retweet" style="color: {{ request('tab') == 'reposts' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i>
        </a>
        <a href="?tab=collections" style="text-decoration: none;">
            <i class="fas fa-layer-group" style="color: {{ request('tab') == 'collections' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i>
        </a>
    </div>

    <!-- Grade 4:5 Dinâmica (Posts, Reposts e Coleções) -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; padding-bottom: 50px;">
        @php
            $tab = request('tab', 'posts');
            if($tab == 'reposts') {
                $items = $profile->shares()->with('status')->get();
            } elseif($tab == 'collections') {
                $items = $profile->collections;
            } else {
                $items = $profile->statuses;
            }
        @endphp

        @foreach($items as $item)
            <div style="aspect-ratio: 4/5; background: #1a1a1a; overflow: hidden;">
                @php
                    if($tab == 'reposts') {
                        $imgUrl = $item->status->mediaUrl() ?? '';
                    } elseif($tab == 'collections') {
                        $imgUrl = $item->coverUrl();
                    } else {
                        $imgUrl = $item->mediaUrl();
                    }
                @endphp
                <img src="{{ $imgUrl }}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        @endforeach
    </div>
</div>

<style>
    /* Configurações Globais de Estética */
    body { background-color: #000 !important; }
    .highlights-row::-webkit-scrollbar { display: none; }

    /* Remoção da Tela "View Profile" nos Stories */
    .story-interstitial, 
    .story-profile-overlay, 
    .story-blur-bg, 
    #story-view-profile-btn { 
        display: none !important; 
        visibility: hidden !important; 
    }

    /* Garantia de Stories Limpos e Nítidos */
    .story-content-wrapper { 
        filter: none !important; 
        opacity: 1 !important; 
    }
</style>
@endsection
