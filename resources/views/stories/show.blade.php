@extends('layouts.app')

@section('content')
<div class="story-viewer-wrapper" style="background: #000; height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <div style="position: absolute; top: 10px; width: 95%; display: flex; gap: 5px; z-index: 10;">
        <div style="flex: 1; height: 2px; background: rgba(255,255,255,0.5); border-radius: 2px;">
            <div style="width: 100%; height: 100%; background: #fff;"></div>
        </div>
    </div>

    <div style="position: absolute; top: 30px; left: 15px; display: flex; align-items: center; gap: 10px; z-index: 10;">
        <img src="{{ $story->profile->avatarUrl() }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
        <span style="color: #fff; font-weight: 600; font-size: 14px;">{{ $story->profile->username }}</span>
    </div>

    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
        @if(Str::contains($story->mime_type, 'video'))
            <video src="{{ $story->mediaUrl() }}" autoplay playsinline muted style="max-width: 100%; max-height: 100%; object-fit: contain;"></video>
        @else
            <img src="{{ $story->mediaUrl() }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
        @endif
    </div>

</div>

<script>
    // Registrar visualização imediatamente ao carregar o componente do story
    document.addEventListener("DOMContentLoaded", function() {
        fetch('/api/v2/stories/seen', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: '{{ $story->id }}' })
        })
        .catch(err => console.error("Erro ao computar visualização do story:", err));
    });

    let selectedStories = [];

    function selectStory(element, id) {
        element.classList.toggle('selected');
        if (selectedStories.includes(id)) {
            selectedStories = selectedStories.filter(sid => sid !== id);
        } else {
            selectedStories.push(id);
        }
    }

    function saveToHighlight() {
        if (selectedStories.length === 0) return alert("Selecione mídias para compor o destaque!");

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
                alert('Destaque gerado com sucesso!');
                selectedStories = [];
                window.location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Tente novamente'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Falha interna de comunicação.');
        });
    }
</script>
@endsection
