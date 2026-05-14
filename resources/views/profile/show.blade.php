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
<div id="rpgram-highlights" class="container" style="background: red; padding: 15px; color: white; font-weight: bold; text-align: center; margin-bottom: 20px;">
    @if(isset($highlights))
        O CÓDIGO ESTÁ FUNCIONANDO! Destaques encontrados: {{ $highlights->count() }}
    @else
        ERRO: A VARIÁVEL AINDA NÃO CHEGOU AQUI.
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

@push('scripts')<script type="text/javascript" src="{{ mix('js/profile.js') }}"></script>
		<script type="text/javascript" defer>App.boot();</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tenta encaixar os destaques várias vezes, caso o Vue demore a carregar
    let tentativas = 0;
    const interval = setInterval(function() {
        const highlights = document.getElementById('rpgram-highlights');
        // No layout Metro, procuramos pela bio ou pela caixa de botões
        const target = document.querySelector('.profile-bio') || 
                       document.querySelector('.profile-header-info') || 
                       document.querySelector('.profile-buttons-wrapper');
        
        if (highlights && target) {
            target.parentNode.insertBefore(highlights, target.nextSibling);
            highlights.style.display = 'block';
            clearInterval(interval);
        }
        
        tentativas++;
        if (tentativas > 10 && highlights) {
            // Se falhar 10 vezes, mostra no topo mesmo para não sumir
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
