@extends('settings.template')

@section('section')
<div class="title">
    <h3 class="font-weight-bold">Alterar Username</h3>
</div>
<hr>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    @foreach($errors->all() as $error)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ $error }}
        </div>
    @endforeach
@endif

<div class="card shadow-none border mb-4">
    <div class="card-body">
        <p class="text-muted small mb-3">
            <i class="fas fa-info-circle mr-1"></i>
            O username atual é <strong>@{{ $user->username }}</strong>.
            Após a troca, você precisará esperar <strong>3 dias</strong> para trocar novamente.
            O username anterior ficará disponível para outros usuários.
        </p>

        @if($canChange)
            <form method="POST" action="/settings/username">
                @csrf

                <div class="form-group">
                    <label class="font-weight-bold small text-muted">Novo username</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">@</span>
                        </div>
                        <input type="text"
                               name="username"
                               class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                               value="{{ old('username', $user->username) }}"
                               placeholder="novo_username"
                               minlength="2"
                               maxlength="30"
                               pattern="[a-zA-Z0-9_]+"
                               required>
                    </div>
                    <small class="text-muted">Apenas letras, números e _ (underscores). Mínimo 2, máximo 30 caracteres.</small>
                </div>

                <button type="submit" class="btn btn-primary font-weight-bold"
                        onclick="return confirm('Tem certeza? Seu username atual ficará disponível para outros usuários.')">
                    Salvar novo username
                </button>
            </form>
        @else
            <div class="alert alert-warning mb-0">
                <i class="fas fa-clock mr-2"></i>
                Você trocou o username recentemente. Poderá trocar novamente em <strong>{{ $daysLeft }} dia(s)</strong>.
            </div>
        @endif
    </div>
</div>

<div class="card shadow-none border">
    <div class="card-body">
        <h6 class="font-weight-bold text-muted mb-2">Sobre a troca de username</h6>
        <ul class="small text-muted mb-0">
            <li>Links antigos com seu username anterior podem parar de funcionar.</li>
            <li>Seu username anterior ficará disponível para outros após a troca.</li>
            <li>Seguidores e posts são mantidos normalmente.</li>
            <li>Você pode trocar a cada 3 dias.</li>
        </ul>
    </div>
</div>
@endsection
