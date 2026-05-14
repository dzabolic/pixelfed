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

<div id="rpgram-highlights" style="display: none; margin: 20px 0; border-bottom: 1px solid #333; padding-bottom: 20px;">
    <div style="display: flex; overflow-x: auto; gap: 15px; padding: 0 10px; scrollbar-width: none; align-items: flex-start;">
        
        <!-- Botão de Novo Destaque (Apenas para o dono do perfil) -->
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <div style="flex: 0 0 auto; text-align: center; width: 85px;">
                <a href="#" onclick="alert('Abrir modal de criação...'); return false;" style="text-decoration: none;">
                    <div style="width: 77px; height: 77px; border-radius: 50%; border: 1px solid #dbdbdb; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; background: transparent;">
                        <span style="font-size: 30px; color: #dbdbdb; font-weight: 200;">+</span>
                    </div>
                    <span style="font-size: 12px; color: #efefef; font-weight: 400; display: block;">Novo</span>
                </a>
            </div>
        @endif

        <!-- Exibição dos Destaques Existentes -->
        @if(isset($highlights))
            @foreach($highlights as $highlight)
                <div style="flex: 0 0 auto; text-align: center; width: 85px;">
                    <a href="/p/highlights/{{ $highlight->id }}" style="text-decoration: none;">
                        <div style="width: 77px; height: 77px; border-radius: 50%; border: 2px solid #dbdbdb; padding: 3px; margin: 0 auto 8px; background: #000;">
                            <img src="{{ $highlight->cover_path ?? 'https://images.unsplash.com/photo-1519074063912-ad25b57b6d17?auto=format&fit=crop&q=80&w=150' }}" 
                                 style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                        </div>
                        <span style="font-size: 12px; color: #efefef; font-weight: 400; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $highlight->title }}
                        </span>
                    </a>
                </div>
            @endforeach
        @endif
    </div>
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
