@extends('layouts.app')

@section('content')
<div id="rpgram-custom-profile" style="background-color: #000; color: #fff; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
    
    <!-- Barra Superior Fixa -->
    <nav style="display: flex; align-items: center; justify-content: center; padding: 10px 16px; border-bottom: 1px solid #262626; position: sticky; top: 0; background: #000; z-index: 100;">
        <span style="font-weight: 700; font-size: 15px;">{{ $profile->username ?? 'perfil' }}</span>

        <!-- BOTÃO DE TROCAR CONTAS (absoluto, não mexe no layout) -->
        @if(Auth::check() && Auth::id() == $profile->user_id)
        <button onclick="openAccountMenu()" style="background: transparent; border: none; cursor: pointer; padding: 4px;">
            <i class="fas fa-bars" style="color: #fff; font-size: 20px;"></i>
        </button>
        @else
        <span style="width: 24px;"></span>
        @endif
    </nav>

    <!-- Cabeçalho Principal -->
    <header style="padding: 16px 16px 0; display: flex; flex-direction: column;">
        <div style="display: flex; align-items: center; margin-bottom: 12px;">
            <div style="margin-right: 28px;">
                <div class="{{ $profile->has_stories ? 'story-ring' : '' }}" style="width: 80px; height: 80px; border-radius: 50%; border: {{ $profile->has_stories ? '2px solid #d62976' : '1px solid #333' }}; padding: 2px;">
                    <img src="{{ $profile->avatarUrl() }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                </div>
            </div>
            <div style="display: flex; flex-grow: 1; justify-content: space-around; text-align: center;">
                <div><strong style="display: block; font-size: 15px;">{{ $profile->statuses_count ?? 0 }}</strong><span style="font-size: 12px; color: #a8a8a8;">posts</span></div>
                <div><strong style="display: block; font-size: 15px;">{{ $profile->followers_count ?? 0 }}</strong><span style="font-size: 12px; color: #a8a8a8;">seguidores</span></div>
                <div><strong style="display: block; font-size: 15px;">{{ $profile->following_count ?? 0 }}</strong><span style="font-size: 12px; color: #a8a8a8;">seguindo</span></div>
            </div>
        </div>

        <!-- Nome e Bio -->
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

    <!-- ===== LINHA DE DESTAQUES ===== -->
    <div class="highlights-row" style="display: flex; overflow-x: auto; padding: 0 16px 20px; gap: 14px; scrollbar-width: none;">
        
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <!-- Botão Novo Destaque -->
            <div style="flex: 0 0 auto; text-align: center; width: 68px;" onclick="openCreateModal()">
                <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; display: flex; align-items: center; justify-content: center; margin-bottom: 5px; cursor: pointer; background: #000;">
                    <span style="font-size: 26px; font-weight: 200;">+</span>
                </div>
                <span style="font-size: 11px;">Novo</span>
            </div>
        @endif

        <!-- Destaques existentes -->
        @if($profile->user && $profile->user->highlights->count() > 0)
            @foreach($profile->user->highlights as $h)
                <div style="flex: 0 0 auto; text-align: center; width: 68px; cursor: pointer;"
                     onclick="openOptionsModal({{ $h->id }}, '{{ addslashes($h->title) }}')">
                    <div style="width: 62px; height: 62px; border-radius: 50%; border: 1px solid #333; padding: 2px; margin-bottom: 5px;">
                        <img src="{{ $h->cover_url ?? '/storage/avatars/default.png' }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    </div>
                    <span style="font-size: 11px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $h->title }}</span>
                </div>
            @endforeach
        @endif
    </div>

    <!-- ===== MODAL: CRIAR NOVO DESTAQUE ===== -->
    <div id="highlightModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #121212; width: 90%; max-width: 400px; border-radius: 12px; padding: 20px; border: 1px solid #333; max-height: 90vh; overflow-y: auto;">
            <h3 id="modalTitle" style="font-size: 16px; font-weight: 600; text-align: center; margin-bottom: 20px;">Novo Destaque</h3>
            
            <!-- Capa e Nome -->
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

            <!-- Seleção de Stories -->
            <h4 style="font-size: 13px; color: #a8a8a8; margin-bottom: 15px;">Selecionar Stories do Arquivo</h4>
            @php
                $myStories = \App\Story::whereProfileId($profile->id)->latest()->get();
            @endphp
            <div id="storyArchive" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; max-height: 250px; overflow-y: auto; padding-right: 5px;">
                @forelse($myStories as $story)
                    <div data-id="{{ $story->id }}" style="aspect-ratio: 9/16; background: #1a1a1a; position: relative; border-radius: 4px; cursor: pointer;" onclick="toggleStorySelect(this)">
                        <img src="{{ $story->mediaUrl() }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; opacity: 0.6;">
                        <div class="check-indicator" style="position: absolute; top: 5px; right: 5px; width: 18px; height: 18px; border: 2px solid #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check" style="font-size: 10px; display: none;"></i>
                        </div>
                    </div>
                @empty
                    <p style="color: #888; font-size: 13px; grid-column: span 3; text-align: center; padding: 20px;">Você ainda não tem stories arquivados.</p>
                @endforelse
            </div>

            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button onclick="closeCreateModal()" style="flex: 1; background: transparent; border: 1px solid #333; color: #fff; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Cancelar</button>
                <button id="finalSaveBtn" style="flex: 1; background: #fff; border: none; color: #000; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer;">Salvar</button>
            </div>
        </div>
    </div>

    <!-- ===== MODAL: OPÇÕES DO DESTAQUE JÁ CRIADO ===== -->
    <div id="highlightOptionsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: flex-end; justify-content: center;">
        <div style="background: #1c1c1e; width: 100%; max-width: 400px; border-radius: 14px 14px 0 0; overflow: hidden; margin: 0 auto;">
            <div style="padding: 16px; text-align: center; border-bottom: 1px solid #2c2c2e;">
                <span id="highlightOptionsTitle" style="font-size: 13px; color: #8e8e8e; font-weight: 600;"></span>
            </div>
            <button onclick="viewHighlightStories()" style="width: 100%; background: transparent; border: none; border-bottom: 1px solid #2c2c2e; color: #fff; padding: 16px; font-size: 16px; cursor: pointer; text-align: center;">
                Ver destaque
            </button>
            @if(Auth::check() && Auth::id() == $profile->user_id)
            <button onclick="editHighlight()" style="width: 100%; background: transparent; border: none; border-bottom: 1px solid #2c2c2e; color: #fff; padding: 16px; font-size: 16px; cursor: pointer; text-align: center;">
                Editar destaque
            </button>
            <button onclick="deleteHighlight()" style="width: 100%; background: transparent; border: none; border-bottom: 1px solid #2c2c2e; color: #ed4956; padding: 16px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center;">
                Apagar destaque
            </button>
            @endif
            <button onclick="closeOptionsModal()" style="width: 100%; background: transparent; border: none; color: #fff; padding: 16px; font-size: 16px; cursor: pointer; text-align: center;">
                Cancelar
            </button>
        </div>
    </div>

    <!-- Abas e Grid de Posts -->
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

{{-- GAVETA DO MENU (três tracinhos) --}}
<div id="accountMenuDrawer" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000;">
    
    {{-- Fundo escuro clicável para fechar --}}
    <div onclick="closeAccountMenu()" style="position: absolute; inset: 0; background: rgba(0,0,0,0.5);"></div>
 
    {{-- Painel lateral direito --}}
    <div style="position: absolute; top: 0; right: 0; width: 80%; max-width: 320px; height: 100%; background: #000; border-left: 1px solid #262626; display: flex; flex-direction: column; overflow-y: auto;">
        
        {{-- Header --}}
        <div style="padding: 20px 16px 10px; border-bottom: 1px solid #262626;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-weight: 700; font-size: 16px; color: #fff;">Contas</span>
                <button onclick="closeAccountMenu()" style="background: transparent; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
            </div>
        </div>
 
        {{-- Lista de contas vinculadas --}}
        <div id="linkedAccountsList" style="flex: 1; padding: 10px 0;">
            {{-- Conta atual (sempre no topo) --}}
            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #111;">
                <div style="position: relative;">
                    <img src="{{ Auth::user()->profile->avatarUrl() }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                    {{-- Bolinha azul indicando conta ativa --}}
                    <div style="position: absolute; bottom: 0; right: 0; width: 14px; height: 14px; background: #3897f0; border-radius: 50%; border: 2px solid #000;"></div>
                </div>
                <div>
                    <div style="font-size: 14px; font-weight: 700; color: #fff;">{{ Auth::user()->username }}</div>
                    <div style="font-size: 12px; color: #3897f0;">Conta ativa</div>
                </div>
            </div>
 
            {{-- Contas vinculadas (carregadas via JS) --}}
            <div id="otherAccountsList" style="padding: 5px 0;">
                <div style="text-align: center; padding: 20px; color: #555; font-size: 13px;">
                    <i class="fas fa-spinner fa-spin"></i> Carregando contas...
                </div>
            </div>
        </div>
 
        {{-- Botão Adicionar Conta --}}
        <div style="border-top: 1px solid #262626; padding: 16px;">
            <a href="/logout?linking=1" 
               onclick="event.preventDefault(); addAccount();"
               style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: #fff; padding: 10px 0;">
                <div style="width: 44px; height: 44px; border-radius: 50%; border: 1px dashed #555; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-plus" style="color: #fff; font-size: 16px;"></i>
                </div>
                <span style="font-size: 14px; font-weight: 600;">Adicionar conta</span>
            </a>
        </div>
 
    </div>
</div>

<!-- ===== TODOS OS SCRIPTS ===== -->
<script>
    // ---- Estado global ----
    let activeHighlightId   = null;
    let activeHighlightMode = 'create'; // 'create' ou 'edit'

    // ---- Utilitários de seleção de story ----
    function toggleStorySelect(el) {
        const check = el.querySelector('.fas.fa-check');
        const img   = el.querySelector('img');
        if (check.style.display === 'none') {
            check.style.display = 'block';
            img.style.opacity   = '1';
            el.style.border     = '2px solid #fff';
        } else {
            check.style.display = 'none';
            img.style.opacity   = '0.6';
            el.style.border     = 'none';
        }
    }

    function getSelectedStoryIds() {
        return Array.from(
            document.querySelectorAll('[data-id] .fas.fa-check[style*="display: block"]')
        ).map(el => el.closest('[data-id]').dataset.id);
    }

    function clearStorySelection() {
        document.querySelectorAll('[data-id]').forEach(el => {
            const check   = el.querySelector('.fas.fa-check');
            const img     = el.querySelector('img');
            check.style.display = 'none';
            img.style.opacity   = '0.6';
            el.style.border     = 'none';
        });
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('coverPreview').innerHTML =
                    '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ---- Modal CRIAR ----
    function openCreateModal() {
        activeHighlightMode = 'create';
        activeHighlightId   = null;
        document.getElementById('modalTitle').innerText     = 'Novo Destaque';
        document.getElementById('highlightName').value      = '';
        document.getElementById('coverPreview').innerHTML   = '<i class="fas fa-camera" style="color:#8e8e8e;font-size:20px;"></i>';
        clearStorySelection();
        document.getElementById('highlightModal').style.display = 'flex';
    }

    function closeCreateModal() {
        document.getElementById('highlightModal').style.display = 'none';
    }

    // ---- Modal OPÇÕES (ver/editar/apagar) ----
    function openOptionsModal(id, title) {
        activeHighlightId = id;
        document.getElementById('highlightOptionsTitle').innerText = title;
        document.getElementById('highlightOptionsModal').style.display = 'flex';
    }

    function closeOptionsModal() {
        document.getElementById('highlightOptionsModal').style.display = 'none';
        activeHighlightId = null;
    }

    // ---- Ver destaque ----
    function viewHighlightStories() {
        window.location.href = '/i/rpgram/highlights/' + activeHighlightId;
    }

    // ---- Editar destaque ----
    function editHighlight() {
        closeOptionsModal();
        activeHighlightMode = 'edit';

        // Busca os dados do destaque para pré-preencher o modal
        fetch('/i/rpgram/highlights/' + activeHighlightId + '/data')
            .then(r => r.json())
            .then(data => {
                document.getElementById('modalTitle').innerText   = 'Editar Destaque';
                document.getElementById('highlightName').value    = data.title;
                document.getElementById('coverPreview').innerHTML =
                    '<img src="' + data.cover_url + '" style="width:100%;height:100%;object-fit:cover;">';

                // Marca os stories que já estão no destaque
                clearStorySelection();
                data.story_ids.forEach(sid => {
                    const el = document.querySelector('[data-id="' + sid + '"]');
                    if (el) toggleStorySelect(el);
                });

                document.getElementById('highlightModal').style.display = 'flex';
            })
            .catch(() => alert('Erro ao carregar destaque.'));
    }

    // ---- Salvar (criar ou editar) ----
    document.getElementById('finalSaveBtn').onclick = function() {
        const name     = document.getElementById('highlightName').value;
        const selected = getSelectedStoryIds();

        if (!name)              { alert('Dê um nome ao destaque.'); return; }
        if (!selected.length)   { alert('Selecione pelo menos um story.'); return; }

        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        this.disabled  = true;

        const isEdit = activeHighlightMode === 'edit';
        const url    = isEdit
            ? '/i/rpgram/highlights/' + activeHighlightId + '/update'
            : '/i/rpgram/highlights/create';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ title: name, items: selected })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(isEdit ? 'Destaque atualizado!' : 'Destaque criado com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Tente novamente'));
                this.innerHTML = 'Salvar';
                this.disabled  = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro de conexão. Veja o console.');
            this.innerHTML = 'Salvar';
            this.disabled  = false;
        });
    };

    // ---- Apagar destaque ----
    function deleteHighlight() {
        if (!confirm('Tem certeza que quer apagar este destaque?')) return;
        fetch('/i/rpgram/highlights/' + activeHighlightId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeOptionsModal();
                location.reload();
            } else {
                alert('Erro ao apagar. Tente novamente.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro de conexão.');

         function openAccountMenu() {
        const drawer = document.getElementById('accountMenuDrawer');
        drawer.style.display = 'block';
        loadLinkedAccounts();
    }
 
    function closeAccountMenu() {
        document.getElementById('accountMenuDrawer').style.display = 'none';
    }
 
    function loadLinkedAccounts() {
        fetch('/i/rpgram/linked-accounts')
            .then(r => r.json())
            .then(accounts => {
                const container = document.getElementById('otherAccountsList');
 
                if (accounts.length === 0) {
                    container.innerHTML = '<div style="text-align: center; padding: 20px; color: #555; font-size: 13px;">Nenhuma conta vinculada ainda.<br>Clique em "Adicionar conta" para começar.</div>';
                    return;
                }
 
                container.innerHTML = accounts.map(acc => `
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #111;"
                         onclick="switchToAccount('${acc.switch_token}')">
                        <img src="${acc.avatar}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                        <div style="flex: 1;">
                            <div style="font-size: 14px; font-weight: 600; color: #fff;">@${acc.username}</div>
                        </div>
                        <button onclick="event.stopPropagation(); unlinkAccount(${acc.id}, this)" 
                                style="background: transparent; border: none; color: #555; font-size: 16px; cursor: pointer; padding: 4px 8px;"
                                title="Remover da lista">
                            &times;
                        </button>
                    </div>
                `).join('');
            })
            .catch(() => {
                document.getElementById('otherAccountsList').innerHTML = 
                    '<div style="text-align: center; padding: 20px; color: #ed4956; font-size: 13px;">Erro ao carregar contas.</div>';
            });
    }
 
    function switchToAccount(token) {
        window.location.href = '/i/rpgram/switch-account/' + token;
    }
 
    function unlinkAccount(userId, btn) {
        if (!confirm('Remover esta conta da lista?')) return;
 
        fetch('/i/rpgram/linked-accounts/' + userId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Remove o item da lista visualmente
                btn.closest('div[style*="padding: 12px 16px"]').remove();
        });
    }
 
    function addAccount() {
        // Faz logout sinalizando que é para vincular uma nova conta
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/logout';
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <input type="hidden" name="linking" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }       
        });
    }
</script>

<style>
    body { background-color: #000 !important; }
    .highlights-row::-webkit-scrollbar { display: none; }
    .story-interstitial, .story-profile-overlay, .story-blur-bg, #story-view-profile-btn { display: none !important; visibility: hidden !important; }
    .story-content-wrapper { filter: none !important; opacity: 1 !important; }
    .story-ring {
        background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        padding: 2px;
        display: inline-block;
    }
    .stat-count:empty::before { content: "0"; }
</style>
@endsection
