@extends('layouts.app')

@section('content')
<div id="rpgram-custom-profile" style="background-color: #000; color: #fff; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
    
    <!-- Cabeçalho Estilo IG (Fontes Menores) -->
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

        <div style="font-size: 13px; line-height: 17px; margin-bottom: 16px;">
            <div style="font-weight: 600; margin-bottom: 2px;">{{ $profile->display_name }}</div>
            <div style="white-space: pre-wrap;">{!! $profile->bio !!}</div>
        </div>

        <!-- Botões de Ação -->
        <div style="display: flex; gap: 8px; margin-bottom: 16px;">
            @if(Auth::check() && Auth::id() == $profile->user_id)
                <a href="{{ route('settings') }}" style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 6px 0; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Editar perfil</a>
                <a href="#" style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 6px 0; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Compartilhar perfil</a>
            @endif
        </div>
    </header>

    <!-- Destaques com Botão "+" Funcional -->
    <div class="highlights-row" style="display: flex; overflow-x: auto; padding: 0 16px 16px; gap: 14px; scrollbar-width: none;">
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <div style="flex: 0 0 auto; text-align: center; width: 68px;">
                <!-- O link abaixo abre a criação de destaques nativa -->
                <a href="/i/web/profile/{{ $profile->id }}/highlights/create" style="text-decoration: none; color: #fff;">
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

    <!-- Abas Originais (Ícones do Pixelfed) -->
    <div style="display: flex; justify-content: space-around; border-top: 1px solid #262626; padding: 12px 0;">
        <a href="?tab=posts" style="text-decoration: none;">
            <i class="fas fa-th" style="color: {{ !request('tab') || request('tab') == 'posts' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i>
        </a>
        <a href="?tab=collections" style="text-decoration: none;">
            <i class="fas fa-layer-group" style="color: {{ request('tab') == 'collections' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i>
        </a>
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <a href="?tab=saved" style="text-decoration: none;">
                <i class="fas fa-bookmark" style="color: {{ request('tab') == 'saved' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i>
            </a>
            <a href="?tab=archives" style="text-decoration: none;">
                <i class="fas fa-archive" style="color: {{ request('tab') == 'archives' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i>
            </a>
        @endif
    </div>

    <!-- Grade 4:5 e Conteúdo Dinâmico -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; padding-bottom: 50px;">
        @php
            $tab = request('tab', 'posts');
            $items = ($tab == 'collections') ? $profile->collections : $profile->statuses;
        @endphp

        @foreach($items as $item)
            <div style="aspect-ratio: 4/5; background: #1a1a1a; overflow: hidden;">
                <img src="{{ $tab == 'collections' ? $item->coverUrl() : $item->mediaUrl() }}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        @endforeach
    </div>
</div>

<style>
    body { background-color: #000 !important; }
    .highlights-row::-webkit-scrollbar { display: none; }
    /* Limpeza de Stories */
    .story-interstitial, .story-profile-overlay, .story-blur-bg, #story-view-profile-btn { display: none !important; visibility: hidden !important; }
    .story-content-wrapper { filter: none !important; opacity: 1 !important; }
</style>
@endsection
