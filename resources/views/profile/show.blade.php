@extends('layouts.app')

@section('content')
<div id="rpgram-custom-profile" style="background-color: #000; color: #fff; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
    
    <!-- Barra Superior Fixa (@ no topo) -->
    <nav style="display: flex; align-items: center; justify-content: center; padding: 10px 16px; border-bottom: 1px solid #262626; position: sticky; top: 0; background: #000; z-index: 100;">
        <span style="font-weight: 700; font-size: 15px;">{{ $profile->username ?? 'perfil' }}</span>
    </nav>

    <!-- Cabeçalho Principal -->
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
                <button style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 7px 0; border-radius: 8px; font-size: 13px; font-weight: 600; border: none;">Compartilhar</button>
            @endif
        </div>
    </header>

    <!-- Destaques -->
    <div class="highlights-row" style="display: flex; overflow-x: auto; padding: 0 16px 20px; gap: 14px; scrollbar-width: none;">
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <div style="flex: 0 0 auto; text-align: center; width: 68px;" onclick="document.getElementById('highlightModal').style.display = 'flex'">
                <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; display: flex; align-items: center; justify-content: center; margin-bottom: 5px; cursor: pointer; background: #000;">
                    <span style="font-size: 26px; font-weight: 200;">+</span>
                </div>
                <span style="font-size: 11px;">Novo</span>
            </div>
        @endif
        
        @if(isset($highlights))
            @foreach($highlights as $h)
                <div style="flex: 0 0 auto; text-align: center; width: 68px;">
                    <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; padding: 2px; margin-bottom: 5px;">
                        <img src="{{ method_exists($h, 'coverUrl') ? $h->coverUrl() : $h->cover_path }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    </div>
                    <span style="font-size: 11px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $h->title }}</span>
                </div>
            @endforeach
        @endif
    </div>

<!-- ESQUELETO DO CRIADOR DE DESTAQUES (MODAL EXPANDIDO) -->
<div id="highlightModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #121212; width: 90%; max-width: 400px; border-radius: 12px; padding: 20px; border: 1px solid #333; max-height: 90vh; overflow-y: auto;">
        <h3 style="font-size: 16px; font-weight: 600; text-align: center; margin-bottom: 20px;">Novo Destaque</h3>
        
        <!-- Passo 1: Capa e Nome -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="position: relative; width: 80px; height: 80px; margin: 0 auto 15px; cursor: pointer;" onclick="document.getElementById('coverInput').click()">
                <div id="coverPreview" style="width: 80px; height: 80px; border-radius: 50%; border: 1px dashed #444; display: flex; align-items: center; justify-content: center; background: #1a1a1a; overflow: hidden;">
                    <i class="fas fa-camera" style="color: #8e8e8e; font-size: 20px;"></i>
                </div>
                <input type="file" id="coverInput" style="display: none;" accept="image/*" onchange="previewImage(this)">
            </div>
            <input type="text" id="highlightName" placeholder="Nome do destaque" style="width: 100%; background: #000; border: 1px solid #333; color: #fff; padding: 10px; border-radius: 8px; font-size: 14px; outline: none; text-align: center;">
        </div>

        <hr style="border: 0; border-top: 1px solid #262626; margin: 20px 0;">

        <!-- Passo 2: Seleção do Arquivo de Stories -->
        <h4 style="font-size: 13px; color: #a8a8a8; margin-bottom: 15px;">Selecionar Stories do Arquivo</h4>
        <div id="storyArchive" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; max-height: 250px; overflow-y: auto; padding-right: 5px;">
            <!-- Simulação de Stories do Arquivo -->
            @foreach($profile->statuses->take(12) as $story)
                <div style="aspect-ratio: 9/16; background: #1a1a1a; position: relative; border-radius: 4px; cursor: pointer;" onclick="toggleStorySelect(this)">
                    <img src="{{ $story->mediaUrl() }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; opacity: 0.6;">
                    <div class="check-indicator" style="position: absolute; top: 5px; right: 5px; width: 18px; height: 18px; border: 2px solid #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check" style="font-size: 10px; display: none;"></i>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button onclick="closeHighlightModal()" style="flex: 1; background: transparent; border: 1px solid #333; color: #fff; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Cancelar</button>
            <button id="finalSaveBtn" style="flex: 1; background: #fff; border: none; color: #000; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer;">Salvar</button>
        </div>
    </div>
</div>

<script>
    // Seleção de Stories no Arquivo
    function toggleStorySelect(el) {
        const check = el.querySelector('.fas.fa-check');
        const img = el.querySelector('img');
        if (check.style.display === 'none') {
            check.style.display = 'block';
            img.style.opacity = '1';
            el.style.border = '2px solid #fff';
        } else {
            check.style.display = 'none';
            img.style.opacity = '0.6';
            el.style.border = 'none';
        }
    }

    // Função de Salvar (Lógica para o bucket rpgram-media)
    document.getElementById('finalSaveBtn').onclick = function() {
        const name = document.getElementById('highlightName').value;
        if(!name) { alert('Dê um nome ao seu destaque.'); return; }
        
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        this.disabled = true;

        // Simulando envio para o servidor/S3
        setTimeout(() => {
            alert('Destaque "' + name + '" salvo com sucesso no rpgram-media!');
            location.reload(); // Recarrega para exibir o novo destaque
        }, 1500);
    };

    // Função para ver o destaque e abrir o menu de 3 pontinhos
    function viewHighlight(id) {
        // Aqui abriria o player de stories
        // Se for o dono:
        const isOwner = {{ (Auth::check() && Auth::id() == $profile->user_id) ? 'true' : 'false' }};
        if(isOwner) {
            console.log("Opções de Editar/Excluir liberadas nos 3 pontinhos");
        }
    }
</script>

    <!-- Abas e Grid -->
    <div style="display: flex; justify-content: space-around; border-top: 1px solid #262626; padding: 12px 0;">
        <a href="?tab=posts" style="text-decoration: none;"><i class="fas fa-th" style="color: #fff; font-size: 20px;"></i></a>
        <a href="?tab=reposts" style="text-decoration: none;"><i class="fas fa-retweet" style="color: #8e8e8e; font-size: 20px;"></i></a>
        <a href="?tab=collections" style="text-decoration: none;"><i class="fas fa-layer-group" style="color: #8e8e8e; font-size: 20px;"></i></a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; padding-bottom: 50px;">
        @foreach($profile->statuses as $status)
            <div style="aspect-ratio: 4/5; background: #1a1a1a; overflow: hidden;">
                <img src="{{ $status->mediaUrl() }}" style="width: 100%; height: 100%; object-fit: cover;">
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
</script>

<style>
    body { background-color: #000 !important; }
    .highlights-row::-webkit-scrollbar { display: none; }
    .story-interstitial, .story-profile-overlay, .story-blur-bg, #story-view-profile-btn { display: none !important; visibility: hidden !important; }
    .story-content-wrapper { filter: none !important; opacity: 1 !important; }
</style>
@endsection
