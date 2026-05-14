@extends('layouts.app')

@section('content')
<div id="rpgram-custom-profile" style="background-color: #000; color: #fff; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
    
    <!-- Cabeçalho (Fiel à image_848533.jpg) -->
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
            <div style="font-weight: 700; font-size: 14px; margin-bottom: 2px;">{{ $profile->name ?? ($profile->display_name ?? $profile->username) }}</div>
            <div style="white-space: pre-wrap; color: #efefef;">{!! $profile->bio !!}</div>
        </div>

        <div style="display: flex; gap: 8px; margin-bottom: 16px;">
            @if(Auth::check() && Auth::id() == $profile->user_id)
                <a href="{{ route('settings') }}" style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 6px 0; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Editar perfil</a>
                <button style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 6px 0; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer;">Compartilhar</button>
            @endif
        </div>
    </header>

    <!-- Destaques -->
    <div class="highlights-row" style="display: flex; overflow-x: auto; padding: 0 16px 16px; gap: 14px; scrollbar-width: none;">
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <div style="flex: 0 0 auto; text-align: center; width: 68px;" onclick="openHighlightModal()">
                <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; display: flex; align-items: center; justify-content: center; margin-bottom: 5px; cursor: pointer;">
                    <span style="font-size: 26px; font-weight: 200;">+</span>
                </div>
                <span style="font-size: 11px;">Novo</span>
            </div>
        @endif
        
        @foreach($highlights as $h)
            <div style="flex: 0 0 auto; text-align: center; width: 68px;">
                <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; padding: 2px; margin-bottom: 5px;">
                    <img src="{{ $h->coverUrl() }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                </div>
                <span style="font-size: 11px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $h->title }}</span>
            </div>
        @endforeach
    </div>

    <!-- Abas e Grid 4:5 permanecem iguais... -->
    <div style="display: flex; justify-content: space-around; border-top: 1px solid #262626; padding: 12px 0;">
        <a href="?tab=posts" style="text-decoration: none;"><i class="fas fa-th" style="color: #fff; font-size: 20px;"></i></a>
        <a href="?tab=reposts" style="text-decoration: none;"><i class="fas fa-retweet" style="color: #8e8e8e; font-size: 20px;"></i></a>
        <a href="?tab=collections" style="text-decoration: none;"><i class="fas fa-layer-group" style="color: #8e8e8e; font-size: 20px;"></i></a>
    </div>

    <!-- O ESQUELETO (MODAL) DE CRIAÇÃO -->
    <div id="highlightModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #121212; width: 90%; max-width: 400px; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #333;">
            <h3 style="font-size: 16px; margin-bottom: 20px;">Novo Destaque</h3>
            
            <!-- Círculo com Camerazinha -->
            <div style="position: relative; width: 80px; height: 80px; margin: 0 auto 20px; cursor: pointer;" onclick="document.getElementById('coverInput').click()">
                <div style="width: 80px; height: 80px; border-radius: 50%; border: 1px dashed #555; display: flex; align-items: center; justify-content: center; background: #1a1a1a;">
                    <i class="fas fa-camera" style="color: #8e8e8e; font-size: 20px;"></i>
                </div>
                <input type="file" id="coverInput" style="display: none;" accept="image/*">
            </div>

            <!-- Campo Nome -->
            <input type="text" id="highlightName" placeholder="Nome do destaque" style="width: 100%; background: #000; border: 1px solid #333; color: #fff; padding: 10px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; outline: none;">

            <!-- Botões -->
            <div style="display: flex; gap: 10px;">
                <button onclick="closeHighlightModal()" style="flex: 1; background: transparent; border: 1px solid #333; color: #fff; padding: 10px; border-radius: 8px; font-size: 14px;">Cancelar</button>
                <button id="saveHighlight" style="flex: 1; background: #fff; border: none; color: #000; padding: 10px; border-radius: 8px; font-size: 14px; font-weight: 600;">Salvar</button>
            </div>
        </div>
    </div>

    <!-- Grid de fotos... -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px;">
        @foreach($profile->statuses as $status)
            <div style="aspect-ratio: 4/5; background: #1a1a1a;">
                <img src="{{ $status->mediaUrl() }}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        @endforeach
    </div>
</div>

<script>
    function openHighlightModal() { document.getElementById('highlightModal').style.display = 'flex'; }
    function closeHighlightModal() { document.getElementById('highlightModal').style.display = 'none'; }
    
    // O 'Salvar' vai precisar de uma rota no backend para o S3
    document.getElementById('saveHighlight').onclick = function() {
        alert('Enviando para o S3...'); 
        // Aqui entra a lógica de upload que faremos a seguir
    };
</script>

<style>
    body { background-color: #000 !important; }
    .highlights-row::-webkit-scrollbar { display: none; }
    .story-interstitial, .story-profile-overlay, .story-blur-bg, #story-view-profile-btn { display: none !important; visibility: hidden !important; }
    .story-content-wrapper { filter: none !important; opacity: 1 !important; }
</style>
@endsection
