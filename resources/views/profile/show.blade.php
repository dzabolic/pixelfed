@extends('layouts.app')

@section('content')
<div id="rpgram-custom-profile" style="background-color: #000; color: #fff; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
    
    <!-- 1. BARRA SUPERIOR FIXA (Username @ no topo) -->
    <nav style="display: flex; align-items: center; justify-content: center; padding: 10px 16px; border-bottom: 1px solid #262626; position: sticky; top: 0; background: #000; z-index: 100;">
        <span style="font-weight: 700; font-size: 14px;">{{ $profile->username ?? '' }}</span>
    </nav>

    <!-- 2. CABEÇALHO (Avatar e Stats) -->
    <header style="padding: 16px 16px 0; display: flex; flex-direction: column;">
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

        <!-- Nome em Negrito e Bio -->
        <div style="font-size: 13px; line-height: 17px; margin-bottom: 16px; padding: 0 4px;">
            <div style="font-weight: 700; font-size: 14px; margin-bottom: 2px;">{{ $profile->name ?? ($profile->display_name ?? $profile->username) }}</div>
            <div style="white-space: pre-wrap; color: #efefef;">{!! $profile->bio !!}</div>
        </div>

        <div style="display: flex; gap: 8px; margin-bottom: 20px;">
            @if(Auth::check() && Auth::id() == $profile->user_id)
                <a href="{{ route('settings') }}" style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 7px 0; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Editar perfil</a>
                <button style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 7px 0; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer;">Compartilhar</button>
            @endif
        </div>
    </header>

    <!-- 3. LINHA DE DESTAQUES (Segura) -->
    <div class="highlights-row" style="display: flex; overflow-x: auto; padding: 0 16px 20px; gap: 14px; scrollbar-width: none;">
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <div style="flex: 0 0 auto; text-align: center; width: 68px;" onclick="document.getElementById('highlightModal').style.display = 'flex'">
                <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; display: flex; align-items: center; justify-content: center; margin-bottom: 5px; cursor: pointer; background: #000;">
                    <span style="font-size: 26px; font-weight: 200;">+</span>
                </div>
                <span style="font-size: 11px;">Novo</span>
            </div>
        @endif
        
        @if(isset($highlights) && count($highlights) > 0)
            @foreach($highlights as $h)
                <div style="flex: 0 0 auto; text-align: center; width: 68px;">
                    <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; padding: 2px; margin-bottom: 5px;">
                        <img src="{{ method_exists($h, 'coverUrl') ? $h->coverUrl() : '' }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    </div>
                    <span style="font-size: 11px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $h->title ?? '' }}</span>
                </div>
            @endforeach
        @endif
    </div>

    <!-- 4. ESQUELETO DO MODAL (Não gera 404) -->
    <div id="highlightModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #121212; width: 90%; max-width: 380px; border-radius: 12px; padding: 25px; border: 1px solid #333; text-align: center;">
            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 25px;">Novo Destaque</h3>
            <div style="position: relative; width: 90px; height: 90px; margin: 0 auto 20px; cursor: pointer;" onclick="document.getElementById('coverInput').click()">
                <div id="coverPreview" style="width: 90px; height: 90px; border-radius: 50%; border: 1px dashed #444; display: flex; align-items: center; justify-content: center; background: #1a1a1a; overflow: hidden;">
                    <i class="fas fa-camera" style="color: #8e8e8e; font-size: 24px;"></i>
                </div>
                <input type="file" id="coverInput" style="display: none;" accept="image/*" onchange="previewImage(this)">
            </div>
            <input type="text" id="highlightName" placeholder="Nome do destaque" style="width: 100%; background: #000; border: 1px solid #333; color: #fff; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 25px; outline: none; text-align: center;">
            <div style="display: flex; gap: 12px;">
                <button onclick="document.getElementById('highlightModal').style.display = 'none'" style="flex: 1; background: transparent; border: 1px solid #333; color: #fff; padding: 12px; border-radius: 8px; font-size: 14px; cursor: pointer;">Cancelar</button>
                <button id="saveHighlightBtn" style="flex: 1; background: #fff; border: none; color: #000; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer;">Salvar</button>
            </div>
        </div>
    </div>

    <!-- 5. ABAS E GRADE -->
    <div style="display: flex; justify-content: space-around; border-top: 1px solid #262626; padding: 12px 0;">
        <a href="?tab=posts" style="text-decoration: none;"><i class="fas fa-th" style="color: {{ !request('tab') || request('tab') == 'posts' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i></a>
        <a href="?tab=reposts" style="text-decoration: none;"><i class="fas fa-retweet" style="color: {{ request('tab') == 'reposts' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i></a>
        <a href="?tab=collections" style="text-decoration: none;"><i class="fas fa-layer-group" style="color: {{ request('tab') == 'collections' ? '#fff' : '#8e8e8e' }}; font-size: 20px;"></i></a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; padding-bottom: 50px;">
        @php
            $tab = request('tab', 'posts');
            $items = ($tab == 'reposts' && isset($profile->shares)) ? $profile->shares : (($tab == 'collections' && isset($profile->collections)) ? $profile->collections : ($profile->statuses ?? []));
        @endphp

        @foreach($items as $item)
            <div style="aspect-ratio: 4/5; background: #1a1a1a; overflow: hidden;">
                @php
                    $imgUrl = ($tab == 'reposts' && isset($item->status)) ? $item->status->mediaUrl() : (($tab == 'collections') ? $item->coverUrl() : $item->mediaUrl());
                @endphp
                <img src="{{ $imgUrl }}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        @endforeach
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('coverPreview').innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover;">';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    document.getElementById('saveHighlightBtn').onclick = function() {
        const name = document.getElementById('highlightName').value;
        if(!name) { alert('Por favor, dê um nome ao destaque.'); return; }
        alert('Enviando para o S3 (rpgram-media): ' + name);
    };
</script>

<style>
    body { background-color: #000 !important; }
    .highlights-row::-webkit-scrollbar { display: none; }
    .story-interstitial, .story-profile-overlay, .story-blur-bg, #story-view-profile-btn { display: none !important; visibility: hidden !important; }
    .story-content-wrapper { filter: none !important; opacity: 1 !important; }
</style>
@endsection
