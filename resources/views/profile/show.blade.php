@extends('layouts.app', [
    'title' => $profile->name . ' (@' . $acct . ') - Pixelfed',
    'ogTitle' => $profile->name . ' (@' . $acct . ')',
    'ogType' => 'profile'
])

@php
$acct = $profile->username . '@' . config('pixelfed.domain.app');
$metaDescription = \App\Services\AccountService::getMetaDescription($profile->id);
@endphp

@section('content')
@if (session('error'))
		<div class="alert alert-danger text-center font-weight-bold mb-0">
				{{ session('error') }}
		</div>
@endif

<!-- Bloco de Destaques com o ID para o script encontrar -->
<!-- Moldura dos Destaques (O Script vai preencher isso) -->
<div id="rpgram-highlights" style="display: none; margin-bottom: 20px;">
    @if(isset($highlights) && $highlights->count() > 0)
        <div class="highlights-container" style="display: flex; overflow-x: auto; padding: 10px 0; gap: 15px; scrollbar-width: none; justify-content: flex-start; border-bottom: 1px solid #dbdbdb; margin-bottom: 10px;">
            @foreach($highlights as $highlight)
                <div class="highlight-item" style="text-align: center; min-width: 85px;">
                    <a href="/p/highlights/{{ $highlight->id }}" style="text-decoration: none; color: #262626;">
                        <div class="highlight-circle" style="width: 77px; height: 77px; border-radius: 50%; border: 1px solid #dbdbdb; padding: 3px; margin: 0 auto 8px; background: #fff;">
                            <img src="{{ $highlight->cover_path ?? '/storage/default-highlight.png' }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        </div>
                        <span style="font-size: 12px; font-weight: 600; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 80px;">
                            {{ $highlight->title }}
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>

<profile profile-id="{{$profile->id}}" profile-username="{{$profile->username}}" :profile-settings="{{json_encode($settings)}}" profile-layout="metro"></profile>

<noscript>
	<div class="container">
		<p class="pt-5 text-center lead">Please enable javascript to view this content.</p>
	</div>
</noscript>

@endsection

@push('meta')<meta name="description" content="{{$metaDescription}}">
    <meta property="og:description" content="{{$metaDescription}}">
    <meta property="og:image" content="{{$profile->avatarUrl()}}">
    <meta property="og:image:width" content="200">
    <meta property="og:image:height" content="200">
    <meta property="twitter:card" content="summary">
    <meta property="profile:username" content="{{$acct}}">
	<link href="{{$profile->permalink('.atom')}}" rel="alternate" title="{{$profile->username}} on Pixelfed" type="application/atom+xml">
	<link href="{{$profile->permalink()}}" rel="alternate" type="application/activity+json">
    <meta name="application-name" content="Pixelfed">
    <meta name="generator" content="pixelfed">
    @if($profile->website)<link href="{{$profile->website}}" rel="me" type="text/html">
@endif
	@if(false == $settings['crawlable'] || $profile->remote_url)<meta name="robots" content="noindex, nofollow">@endif
@endpush

@push('scripts')
<script type="text/javascript" src="{{ mix('js/profile.js') }}"></script>
<script type="text/javascript" defer>App.boot();</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let tentativas = 0;
    const interval = setInterval(function() {
        const highlights = document.getElementById('rpgram-highlights');
        
        // Adicionamos o alvo '.p-3.border-bottom' que é comum no seu layout
        const target = document.querySelector('.profile-bio') || 
                       document.querySelector('.p-3.border-bottom') ||
                       document.querySelector('.profile-header-info') || 
                       document.querySelector('.profile-buttons-wrapper');
        
        if (highlights && target) {
            // Usa o comando 'after' que é mais simples: coloca DEPOIS do alvo
            target.after(highlights);
            highlights.style.display = 'block';
            clearInterval(interval);
        }
        
        tentativas++;
        // Se após 5 segundos não achar o lugar, ele aparece onde estiver para não sumir
        if (tentativas > 10 && highlights) {
            highlights.style.display = 'block';
            clearInterval(interval);
        }
    }, 500);
});
</script>
@endpush

<style>
    /* Isso força o container de destaques a ter uma estética de RPG se quiser, 
       ou apenas garante que ele não quebre o layout metro */
    .highlights-container::-webkit-scrollbar {
        display: none;
    }
    .highlight-circle:hover {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }
</style>
