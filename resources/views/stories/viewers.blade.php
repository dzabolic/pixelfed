@extends('layouts.app')

@section('content')
<div class="viewers-container" style="background: #000; min-height: 100vh; color: #fff; padding-top: 20px;">
    
    <!-- Header da Lista (estilo Imagem 3) -->
    <div style="padding: 0 15px 20px; border-bottom: 1px solid #262626;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <a href="{{ url()->previous() }}" style="color: #fff; text-decoration: none;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <span style="font-weight: 700; font-size: 16px;">Visualizações</span>
            <div style="width: 20px;"></div> 
        </div>

        <!-- Botão de Insights -->
        <div style="display: flex; align-items: center; background: #1a1a1a; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
            <i class="fas fa-chart-line" style="margin-right: 15px; font-size: 18px;"></i>
            <span style="font-size: 14px; font-weight: 600;">Turbinar este story</span>
            <i class="fas fa-chevron-right" style="margin-left: auto; color: #8e8e8e; font-size: 12px;"></i>
        </div>

        <div style="font-weight: 700; font-size: 14px; color: #a8a8a8; margin-top: 10px;">Pessoas que viram seu story</div>
    </div>

    <!-- Lista de Usuários -->
    <div class="viewers-list" style="padding: 15px;">
        @foreach($viewers as $view)
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                
                <!-- Lado Esquerdo: Avatar, Nomes e Horário -->
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="position: relative;">
                        <!-- Avatar -->
                        <img src="{{ $view->profile->avatarUrl() }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                        
                        <!-- Badge de Coração (Reação) -->
                        @if(in_array($view->profile_id, $reactions))
                            <div style="position: absolute; bottom: -2px; right: -2px; background: #ed4956; border: 2px solid #000; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-heart" style="color: #fff; font-size: 9px;"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 14px; font-weight: 700; line-height: 1.2;">{{ $view->profile->username }}</span>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="font-size: 13px; color: #8e8e8e; font-weight: 400;">{{ $view->profile->name }}</span>
                            <span style="color: #555; font-size: 12px;">•</span>
                            <!-- HORÁRIO EXATO DA VISUALIZAÇÃO -->
                            <span style="font-size: 12px; color: #8e8e8e;">{{ $view->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Lado Direito: Opções e Enviar Mensagem -->
                <div style="display: flex; align-items: center; gap: 20px;">
                    <i class="fas fa-ellipsis-v" style="color: #fff; font-size: 16px; cursor: pointer;"></i>
                    <i class="far fa-paper-plane" style="color: #fff; font-size: 18px; cursor: pointer;"></i>
                </div>
            </div>
        @endforeach
    </div>

    @if($viewers->isEmpty())
        <div style="text-align: center; margin-top: 50px; color: #8e8e8e;">
            <i class="fas fa-eye" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i>
            <p>Ninguém viu seu story ainda.</p>
        </div>
    @endif
</div>

<style>
    body {
        margin: 0;
        padding: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
</style>
@endsection
