@extends('layouts.app')

@section('content')
<div id="rpgram-custom-profile" style="background-color: #000; color: #fff; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
    
    <!-- Cabeçalho (Avatar + Status) -->
    <header style="padding: 16px; display: flex; flex-direction: column;">
        <div style="display: flex; align-items: center; margin-bottom: 12px;">
            <div style="margin-right: 28px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; border: 1px solid #333; padding: 2px;">
                    <img src="{{ $profile->avatarUrl() }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                </div>
            </div>
            <div style="display: flex; flex-grow: 1; justify-content: space-around; text-align: center;">
                <div><strong style="display: block; font-size: 16px;">{{ $profile->statuses_count ?? 0 }}</strong> <span style="font-size: 13px; color: #a8a8a8;">posts</span></div>
                <div><strong style="display: block; font-size: 16px;">{{ $profile->followers_count ?? 0 }}</strong> <span style="font-size: 13px; color: #a8a8a8;">seguidores</span></div>
                <div><strong style="display: block; font-size: 16px;">{{ $profile->following_count ?? 0 }}</strong> <span style="font-size: 13px; color: #a8a8a8;">seguindo</span></div>
            </div>
        </div>

        <!-- Bio e Nome (Fontes ajustadas para celular) -->
        <div style="font-size: 14px; line-height: 18px; margin-bottom: 16px;">
            <div style="font-weight: 600; margin-bottom: 2px;">{{ $profile->display_name }}</div>
            <div style="white-space: pre-wrap;">{!! $profile->bio !!}</div>
            @if($profile->website)
                <a href="{{ $profile->website }}" target="_blank" style="color: #e0f1ff; text-decoration: none; font-weight: 500; display: block; margin-top: 4px;">{{ str_replace(['http://', 'https://'], '', $profile->website) }}</a>
            @endif
        </div>

        <!-- Botões de Ação -->
        <div style="display: flex; gap: 8px; margin-bottom: 16px;">
            @if(Auth::check() && Auth::id() == $profile->user_id)
                <a href="{{ route('settings') }}" style="flex: 1; background: #333; color: #fff; text-align: center; padding: 7px 0; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none;">Editar perfil</a>
                <a href="#" style="flex: 1; background: #333; color: #fff; text-align: center; padding: 7px 0; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none;">Compartilhar perfil</a>
            @endif
        </div>
    </header>

    <!-- Destaques (Highlights) com Scroll Lateral -->
    <div class="highlights-row" style="display: flex; overflow-x: auto; padding: 0 16px 16px; gap: 14px; scrollbar-width: none;">
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <div style="flex: 0 0 auto; text-align: center; width: 68px;">
                <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; display: flex; align-items: center; justify-content: center; margin-bottom: 5px;">
                    <span style="font-size: 26px; font-weight: 200; color: #fff;">+</span>
                </div>
                <span style="font-size: 11px;">Novo</span>
            </div>
        @endif

        @if(isset($highlights))
            @foreach($highlights as $h)
                <div style="flex: 0 0 auto; text-align: center; width: 68px;">
                    <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; padding: 2px; margin-bottom: 5px;">
                        <img src="{{ $h->cover_path }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    </div>
                    <span style="font-size: 11px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $h->title }}</span>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Abas de Navegação -->
    <div style="display: flex; justify-content: space-around; border-top: 1px solid #262626; padding: 12px 0;">
        <i class="fas fa-th" style="color: #fff; font-size: 20px;"></i>
        <i class="fas fa-play-circle" style="color: #8e8e8e; font-size: 20px;"></i>
        <i class="fas fa-user-tag" style="color: #8e8e8e; font-size: 20px;"></i>
    </div>

    <!-- Grid de Fotos Proporção 4:5 -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; padding-bottom: 50px;">
        @foreach($profile->statuses as $status)
            <div style="aspect-ratio: 4/5; background: #1a1a1a; overflow: hidden;">
                <img src="{{ $status->mediaUrl() }}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        @endforeach
    </div>
</div>

<style>
    body { background-color: #000 !important; }
    .highlights-row::-webkit-scrollbar { display: none; }
    /* Remove a tela obrigatória dos stories */
    .story-interstitial, .story-profile-overlay, .story-blur-bg { display: none !important; }
    .story-content-wrapper { filter: none !important; opacity: 1 !important; }
</style>
@endsection
