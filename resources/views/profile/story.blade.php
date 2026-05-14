@extends('layouts.blank')

@section('content')
<story-viewer pid="{{$pid}}" redirect-url="{{$profile->url()}}"></story-viewer>
@endsection

@push('scripts')
<script type="text/javascript" src="/js/stories.js?v={{ time() }}"></script>
<script type="text/javascript" src="{{mix('js/profile.js')}}"></script>
<script type="text/javascript">App.boot();</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Injeta o botão de destaque na interface do Story
    setInterval(function() {
        const header = document.querySelector('.story-viewer-header-right'); // Onde ficam os botões de fechar/opções
        if (header && !document.getElementById('btn-destacar-rpgram')) {
            const btn = document.createElement('button');
            btn.id = 'btn-destacar-rpgram';
            btn.innerHTML = '✨ Destacar';
            btn.style = 'background: rgba(255,255,255,0.2); border: 1px solid #fff; color: #fff; padding: 5px 10px; border-radius: 4px; margin-right: 10px; cursor: pointer; font-size: 12px;';
            
            btn.onclick = function() {
                const storyId = window.location.pathname.split('/').pop(); // Pega o ID do story pela URL
                destacarStory(storyId);
            };
            header.prepend(btn);
        }
    }, 1000);
});

function destacarStory(id) {
    fetch('/api/v1/rpgram/highlight', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ story_id: id, title: 'Novo Destaque' })
    })
    .then(res => res.json())
    .then(data => {
        alert('Story adicionado aos destaques do seu perfil!');
    })
    .catch(err => alert('Ocorreu um erro ao salvar. Verifique o console.'));
}
</script>
@endpush
