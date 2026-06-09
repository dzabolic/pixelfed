@extends('layouts.app')

@section('content')
<div id="rpgram-custom-profile" style="background-color: #000; color: #fff; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">

    <nav style="display: flex; align-items: center; justify-content: center; padding: 10px 16px; border-bottom: 1px solid #262626; position: sticky; top: 0; background: #000; z-index: 100;">
        <span style="font-weight: 700; font-size: 15px;">{{ $profile->username ?? 'perfil' }}</span>
    @if(Auth::check() && Auth::id() == $profile->user_id)
        <button onclick="openAccountMenu()"
            style="
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                background: transparent;
                border: none;
                cursor: pointer;
                padding: 4px;
            ">
            <i class="fas fa-bars" style="color: #fff; font-size: 20px;"></i>
        </button>
    @endif
    </nav>

    <header style="padding: 16px 16px 0; display: flex; flex-direction: column;">
        <div style="display: flex; align-items: center; margin-bottom: 16px;">
            
            @php
                $hasStories = \App\Story::whereProfileId($profile->id)->where('created_at', '>=', now()->subHours(24))->exists();
                $allSeen = false;
                if ($hasStories && Auth::check()) {
                    $storyIds = \App\Story::whereProfileId($profile->id)->where('created_at', '>=', now()->subHours(24))->pluck('id');
                    $seenCount = \App\StoryView::whereIn('story_id', $storyIds)->where('profile_id', Auth::user()->profile->id)->count();
                    if ($seenCount >= count($storyIds)) {
                        $allSeen = true;
                    }
                }
                
                $ringStyle = 'background: transparent; border: 1px solid #262626;';
                if ($hasStories) {
                    if ($allSeen) {
                        $ringStyle = 'background: #555; padding: 3px;';
                    } else {
                        $ringStyle = 'background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); padding: 3px;';
                    }
                }
            @endphp
            
            <div style="position: relative; border-radius: 50%; {{ $ringStyle }}">
                <div style="background: #000; border-radius: 50%; padding: 2px; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ $profile->avatarUrl() }}" style="width: 77px; height: 77px; border-radius: 50%; object-fit: cover;">
                </div>
            </div>

            <div style="display: flex; flex-grow: 1; justify-content: space-around; text-align: center;">
                <div>
                    <strong style="display: block; font-size: 15px;">{{ $postsCount ?? 0 }}</strong>
                    <span style="font-size: 12px; color: #a8a8a8;">posts</span>
                </div>
                <a href="/{{ $profile->username }}/followers" style="text-decoration: none; color: inherit;">
                    <strong style="display: block; font-size: 15px;">{{ $followersCount ?? 0 }}</strong>
                    <span style="font-size: 12px; color: #a8a8a8;">seguidores</span>
                </a>
                <a href="/{{ $profile->username }}/following" style="text-decoration: none; color: inherit;">
                    <strong style="display: block; font-size: 15px;">{{ $followingCount ?? 0 }}</strong>
                    <span style="font-size: 12px; color: #a8a8a8;">seguindo</span>
                </a>
            </div>
        </div>

        @if(Auth::check() && Auth::id() != $profile->user_id)
            <div style="margin-top: -8px; margin-bottom: 12px; color: #fff; font-size: 12px; padding-left: 4px; font-weight: 400;">
                @php
                    $followsUs = \App\Follower::whereProfileId($profile->id)->whereFollowingId(Auth::user()->profile->id)->exists();
                @endphp
                @if($followsUs)
                    (segue você)
                @else
                    (não segue você)
                @endif
            </div>
        @endif

        <div style="font-size: 13px; line-height: 17px; margin-bottom: 16px; padding: 0 4px;">
            <div style="font-weight: 700; font-size: 14px; margin-bottom: 2px;">{{ $profile->name ?? ($profile->display_name ?? $profile->username) }}</div>
            <div style="white-space: pre-wrap; color: #efefef;">{!! $profile->bio !!}</div>
        </div>

        <div style="display: flex; gap: 8px; margin-bottom: 20px; padding: 0 4px;">
            @if(Auth::check() && Auth::id() == $profile->user_id)
                <a href="{{ route('settings') }}" style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 7px 0; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Editar perfil</a>
                <button style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 7px 0; border-radius: 8px; font-size: 13px; font-weight: 600; border: none;">Compartilhar</button>
            @elseif(Auth::check())
                @php
                    $isFollowing = \App\Follower::whereProfileId(Auth::user()->profile->id)->whereFollowingId($profile->id)->exists();
                @endphp
                <form action="{{ $isFollowing ? route('unfollow', $profile->id) : route('follow', $profile->id) }}" method="POST" style="flex: 1; display: flex; margin: 0;">
                    @csrf
                    <button type="submit" style="width: 100%; background: {{ $isFollowing ? '#262626' : '#0095f6' }}; color: #fff; text-align: center; padding: 7px 0; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer;">
                        {{ $isFollowing ? 'Seguindo' : 'Seguir' }}
                    </button>
                </form>
                <a href="/i/web/messenger/{{ $profile->id }}" style="flex: 1; background: #262626; color: #fff; text-align: center; padding: 7px 0; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: flex; justify-content: center; align-items: center;">Mensagem</a>
            @endif
        </div>
    </header>

    <div class="highlights-row" style="display: flex; gap: 12px; overflow-x: auto; padding: 0 16px 16px; border-bottom: 1px solid #262626;">
        @if(Auth::check() && Auth::id() == $profile->user_id)
            <div onclick="toggleHighlightArchive()" style="display: flex; flex-direction: column; align-items: center; cursor: pointer; flex-shrink: 0;">
                <div style="width: 56px; height: 56px; border-radius: 50%; border: 1px solid #262626; display: flex; align-items: center; justify-content: center; margin-bottom: 6px;">
                    <i class="fas fa-plus" style="color: #fff; font-size: 18px;"></i>
                </div>
                <span style="font-size: 11px; color: #fff;">Novo</span>
            </div>
        @endif

        @foreach($highlights ?? [] as $highlight)
            <div style="display: flex; flex-direction: column; align-items: center; flex-shrink: 0; position: relative;">
                <a href="/i/rpgram/highlights/{{ $highlight->id }}" style="text-decoration: none; display: flex; flex-direction: column; align-items: center;">
                    <img src="{{ $highlight->cover_url ?? '/storage/default/highlight.png' }}" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 1px solid #262626; margin-bottom: 6px;">
                    <span style="font-size: 11px; color: #fff;">{{ $highlight->title }}</span>
                </a>
                @if(Auth::check() && Auth::id() == $profile->user_id)
                    <a href="/i/rpgram/highlights/{{ $highlight->id }}/edit" style="position: absolute; top: 0; right: 0; background: rgba(0,0,0,0.7); border-radius: 50%; padding: 2px; text-decoration: none;">
                        <i class="fas fa-edit" style="color: #fff; font-size: 10px;"></i>
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
