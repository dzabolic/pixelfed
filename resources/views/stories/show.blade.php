@extends('layouts.app')

@section('content')
<div class="story-viewer-wrapper" style="background: #000; height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <!-- Barra de Progresso Superior -->
    <div style="position: absolute; top: 10px; width: 95%; display: flex; gap: 5px; z-index: 10;">
        <div style="flex: 1; height: 2px; background: rgba(255,255,255,0.5); border-radius: 2px;">
            <div style="width: 100%; height: 100%; background: #fff;"></div>
        </div>
    </div>

    <!-- Header do Story -->
    <div style="position: absolute; top: 30px; left: 15px; display: flex; align-items: center; gap: 10px; z-index: 10;">
        <img src="{{ $story->profile->avatarUrl() }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
        <span style="color: #fff; font-weight: 600; font-size: 14px;">{{ $story->profile->username }}</span>
        <span style="color: rgba(255,255,255,0.6); font-size: 14px;">{{ $story->created_at->diffForHumans(null, true) }}</span>
    </div>

    <!-- Fechar -->
    <a href="/{{ $story->profile->username }}" style="position: absolute; top: 30px; right: 15px; color: #fff; font-size: 24px; z-index: 10; text-decoration: none;">&times;</a>

    <!-- Mídia do Story: detecta foto ou vídeo automaticamente -->
    @php
        $isVideo = in_array($story->mime ?? '', ['video/mp4', 'video/webm', 'video/quicktime', 'video/ogg'])
                   || ($story->type ?? '') === 'video';
    @endphp

    @if($isVideo)
        <video src="{{ $story->mediaUrl() }}"
               autoplay muted playsinline loop
               style="max-width: 100%; max-height: 100vh; object-fit: contain;">
        </video>
    @else
        <img src="{{ $story->mediaUrl() }}" style="max-width: 100%; max-height: 100vh; object-fit: contain;">
    @endif

    @php
        $isOwner = Auth::check() && Auth::id() == $story->profile->user_id;
    @endphp

    <!-- Footer -->
    <div style="position: absolute; bottom: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, rgba(0,0,0,0.8));">
        @if($isOwner)
            <div style="display: flex; align-items: center; gap: 30px;">
                <div onclick="toggleViewerList()" style="display: flex; flex-direction: column; align-items: center; color: #fff; cursor: pointer;">
                    <i class="fas fa-chart-line" style="font-size: 20px; margin-bottom: 5px;"></i>
                    <span style="font-size: 12px; font-weight: 600;">Atividade</span>
                </div>
                <div onclick="toggleHighlightArchive()" style="display: flex; flex-direction: column; align-items: center; color: #fff; cursor: pointer;">
                    <i class="far fa-star" style="font-size: 20px; margin-bottom: 5px;"></i>
                    <span style="font-size: 12px; font-weight: 600;">Destaque</span>
                </div>
                <div style="margin-left: auto; color: #fff; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-eye" style="font-size: 14px;"></i>
                    <span style="font-size: 14px;">{{ $story->view_count }}</span>
                </div>
            </div>
        @else
            <div style="display: flex; align-items: center; gap: 15px;">
                <input type="text" placeholder="Enviar mensagem..." style="flex: 1; background: transparent; border: 1px solid rgba(255,255,255,0.5); border-radius: 25px; padding: 10px 20px; color: #fff; outline: none; font-size: 14px;">
                <i class="far fa-heart" style="color: #fff; font-size: 24px; cursor: pointer;"></i>
                <i class="far fa-paper-plane" style="color: #fff; font-size: 24px; cursor: pointer;"></i>
            </div>
        @endif
    </div>

    @if($isOwner)
    <!-- Gaveta 1: Visualizadores -->
    <div id="viewerList" class="bottom-drawer">
        <div class="drawer-handle" onclick="toggleViewerList()"></div>
        <div style="padding: 15px; font-weight: 700; font-size: 14px; color: #a8a8a8; text-align: center;">Pessoas que viram seu story</div>
        <div class="viewers-scroll" style="overflow-y: auto; height: calc(70vh - 100px); padding: 0 15px;">
            @foreach($story->views as $view)
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="position: relative;">
                        <img src="{{ $view->profile->avatarUrl() }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                        @if(isset($story->reactions) && $story->reactions->where('profile_id', $view->profile_id)->count() > 0)
                            <div style="position: absolute; bottom: -2px; right: -2px; background: #fff; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-heart" style="color: #ed4956; font-size: 10px;"></i>
                            </div>
                        @endif
                    </div>
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: #fff;">{{ $view->profile->username }}</div>
                        <div style="font-size: 13px; color: #8e8e8e;">{{ $view->profile->name }}</div>
                    </div>
                </div>
            </div>
            @endforeach
            @if($story->views->isEmpty())
                <div style="text-align: center; padding: 30px; color: #555;">
                    <i class="fas fa-eye" style="font-size: 28px; margin-bottom: 10px; display: block;"></i>
                    Ninguém viu ainda.
                </div>
            @endif
        </div>
    </div>

    <!-- Gaveta 2: Arquivo para Destaque -->
    <div id="highlightArchive" class="bottom-drawer">
        <div class="drawer-handle" onclick="toggleHighlightArchive()"></div>
        <div style="padding: 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #222;">
            <span style="font-weight: 700;">Selecionar para Destaque</span>
            <button onclick="saveToHighlight()" style="background: #3897f0; border: none; color: #fff; padding: 5px 15px; border-radius: 4px; font-weight: 600; font-size: 13px;">Concluir</button>
        </div>

        <div style="overflow-y: auto; height: calc(70vh - 100px); display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; padding: 2px;">
            @foreach(\App\Story::where('profile_id', $story->profile_id)->latest()->get() as $archive)
                @php
                    $archiveIsVideo = in_array($archive->mime ?? '', ['video/mp4', 'video/webm', 'video/quicktime', 'video/ogg'])
                                      || ($archive->type ?? '') === 'video';
                @endphp
                <div class="archive-item" onclick="selectStory(this, {{ $archive->id }})"
                     style="position: relative; aspect-ratio: 9/16; cursor: pointer; background: #111; overflow: hidden;">

                    @if($archiveIsVideo)
                        {{-- Thumbnail do vídeo: tenta thumbnail_url, senão exibe ícone de play --}}
                        @if($archive->thumbnail_url ?? false)
                            <img src="{{ $archive->thumbnail_url }}" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;">
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #1a1a1a;">
                                <i class="fas fa-play-circle" style="color: #fff; font-size: 32px; opacity: 0.8;"></i>
                            </div>
                        @endif
                        <div style="position: absolute; bottom: 4px; left: 4px;">
                            <i class="fas fa-video" style="color: #fff; font-size: 10px; opacity: 0.9;"></i>
                        </div>
                    @else
                        <img src="{{ url(\Storage::url($archive->path)) }}"
                             style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;"
                             onerror="this.style.display='none'">
                    @endif

                    <div class="check-overlay" style="position: absolute; inset: 0; display: none; background: rgba(56, 151, 240, 0.3); border: 3px solid #3897f0;">
                        <i class="fas fa-check-circle" style="position: absolute; top: 10px; right: 10px; color: #fff; font-size: 16px;"></i>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<style>
    .bottom-drawer {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 70vh;
        background: #121212;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        z-index: 100;
        color: #fff;
        transform: translateY(100%);
        transition: transform 0.3s ease-in-out;
    }
    .drawer-handle { width: 40px; height: 4px; background: #333; border-radius: 2px; margin: 10px auto; cursor: pointer; }
    .viewers-scroll::-webkit-scrollbar { display: none; }
    .archive-item.selected .check-overlay { display: block !important; }
    .archive-item.selected img { opacity: 1 !important; }
</style>

<script>
    let selectedStories = [];

    function toggleViewerList() {
        animateDrawer(document.getElementById('viewerList'));
    }

    function toggleHighlightArchive() {
        animateDrawer(document.getElementById('highlightArchive'));
    }

    function animateDrawer(el) {
        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = 'block';
            setTimeout(() => { el.style.transform = 'translateY(0)'; }, 10);
        } else {
            el.style.transform = 'translateY(100%)';
            setTimeout(() => { el.style.display = 'none'; }, 300);
        }
    }

    function selectStory(element, id) {
        element.classList.toggle('selected');
        if (selectedStories.includes(id)) {
            selectedStories = selectedStories.filter(sid => sid !== id);
        } else {
            selectedStories.push(id);
        }
    }

    function saveToHighlight() {
        if (selectedStories.length === 0) return alert("Selecione pelo menos um story!");

        // Pede o nome do destaque
        const title = prompt("Nome do destaque:", "Meus Destaques");
        if (!title) return;

        fetch('/i/rpgram/highlights/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                items: selectedStories,
                title: title
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Destaque criado com sucesso!');
                toggleHighlightArchive();
                selectedStories = [];
            } else {
                alert('Erro: ' + (data.error || 'Tente novamente'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro de conexão.');
        });
    }
</script>
@endsection
