@extends('layouts.app')

@section('content')
<div class="story-viewer-wrapper" style="background: #000; height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
    
    <!-- Barra de Progresso Superior -->
    <div style="position: absolute; top: 10px; width: 95%; display: flex; gap: 5px; z-index: 10;">
        <div style="flex: 1; height: 2px; background: rgba(255,255,255,0.5); border-radius: 2px;">
            <div style="width: 50%; height: 100%; background: #fff;"></div>
        </div>
    </div>

    <!-- Header do Story (Avatar e Nome) -->
    <div style="position: absolute; top: 30px; left: 15px; display: flex; align-items: center; gap: 10px; z-index: 10;">
        <img src="{{ $story->profile->avatarUrl() }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
        <span style="color: #fff; font-weight: 600; font-size: 14px;">{{ $story->profile->username }}</span>
        <span style="color: rgba(255,255,255,0.6); font-size: 14px;">{{ $story->created_at->diffForHumans(null, true) }}</span>
    </div>

    <!-- Fechar -->
    <a href="/" style="position: absolute; top: 30px; right: 15px; color: #fff; font-size: 24px; z-index: 10; text-decoration: none;">&times;</a>

    <!-- Mídia do Story -->
    <img src="{{ $story->mediaUrl() }}" style="max-width: 100%; max-height: 100vh; object-fit: contain;">

    @php
        $isOwner = Auth::check() && Auth::id() == $story->profile->user_id;
    @endphp

    <!-- FOOTER: DINÂMICO (IMAGENS 1 E 2) -->
    <div style="position: absolute; bottom: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, rgba(0,0,0,0.8));">
        
        @if($isOwner)
            <!-- VISÃO DO DONO (Imagem 2) -->
            <div onclick="toggleViewerList()" style="display: flex; align-items: center; cursor: pointer; width: fit-content;">
                <div style="display: flex; flex-direction: column; align-items: center; color: #fff;">
                    <i class="fas fa-chart-line" style="font-size: 20px; margin-bottom: 5px;"></i>
                    <span style="font-size: 12px; font-weight: 600;">Atividade</span>
                </div>
                <div style="margin-left: 20px; color: #fff; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-eye" style="font-size: 14px;"></i>
                    <span style="font-size: 14px;">{{ $story->view_count }}</span>
                </div>
            </div>
        @else
            <!-- VISÃO DO VISITANTE (Imagem 1) -->
            <div style="display: flex; align-items: center; gap: 15px;">
                <input type="text" placeholder="Enviar mensagem..." style="flex: 1; background: transparent; border: 1px solid rgba(255,255,255,0.5); border-radius: 25px; padding: 10px 20px; color: #fff; outline: none; font-size: 14px;">
                <i class="far fa-heart" style="color: #fff; font-size: 24px; cursor: pointer;"></i>
                <i class="far fa-paper-plane" style="color: #fff; font-size: 24px; cursor: pointer;"></i>
            </div>
        @endif
    </div>

    <!-- LISTA DE VISUALIZADORES: BOTTOM SHEET (IMAGEM 3) -->
    @if($isOwner)
    <div id="viewerList" style="display: none; position: fixed; bottom: 0; left: 0; width: 100%; height: 70vh; background: #121212; border-top-left-radius: 15px; border-top-right-radius: 15px; z-index: 100; color: #fff; transition: transform 0.3s ease-in-out;">
        <div style="text-align: center; padding: 10px;" onclick="toggleViewerList()">
            <div style="width: 40px; height: 4px; background: #333; border-radius: 2px; margin: 0 auto;"></div>
        </div>
        
        <div style="display: flex; justify-content: space-around; padding: 15px; border-bottom: 1px solid #222;">
            <i class="fas fa-chart-bar" style="color: #3897f0;"></i>
            <i class="fas fa-users" style="color: #fff;"></i>
            <i class="fas fa-trash" style="color: #fff;"></i>
        </div>

        <div style="padding: 15px; font-weight: 700; font-size: 14px; color: #a8a8a8;">Pessoas que viram seu story</div>

        <div class="viewers-scroll" style="overflow-y: auto; height: calc(70vh - 120px); padding: 0 15px;">
            @foreach($story->views as $view)
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 12px; position: relative;">
                    <div style="position: relative;">
                        <img src="{{ $view->profile->avatarUrl() }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                        @if($story->reactions->where('profile_id', $view->profile_id)->count() > 0)
                            <div style="position: absolute; bottom: -2px; right: -2px; background: #fff; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-heart" style="color: #ed4956; font-size: 10px;"></i>
                            </div>
                        @endif
                    </div>
                    <div>
                        <div style="font-size: 14px; font-weight: 600;">{{ $view->profile->username }}</div>
                        <div style="font-size: 13px; color: #8e8e8e;">{{ $view->profile->name }}</div>
                    </div>
                </div>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <i class="fas fa-ellipsis-h" style="color: #8e8e8e;"></i>
                    <i class="far fa-paper-plane" style="color: #fff;"></i>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
    function toggleViewerList() {
        const list = document.getElementById('viewerList');
        if (list.style.display === 'none' || list.style.display === '') {
            list.style.display = 'block';
            setTimeout(() => { list.style.transform = 'translateY(0)'; }, 10);
        } else {
            list.style.transform = 'translateY(100%)';
            setTimeout(() => { list.style.display = 'none'; }, 300);
        }
    }
</script>

<style>
    /* Estilo para esconder a barra de scroll mas manter a funcionalidade */
    .viewers-scroll::-webkit-scrollbar { display: none; }
    .viewers-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
