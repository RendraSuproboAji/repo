@php($links = config('site.links'))

<div class="drawer-backdrop" hidden></div>
<aside class="drawer" hidden aria-label="Menu">
    <div class="drawer-header">
        <span class="brand-logo" aria-hidden="true"></span>
        <span class="drawer-title">{{ config('app.name') }}</span>
        <button class="icon-btn drawer-close" aria-label="Tutup menu">
            <svg class="i" viewBox="0 0 24 24"><use href="#i-close"/></svg>
        </button>
    </div>

    <nav class="drawer-nav">
        <a class="drawer-item" href="{{ route('explore') }}">
            <svg class="i" viewBox="0 0 24 24"><use href="#i-explore"/></svg><span>Explore</span>
        </a>
        @if ($links['editor'])
            <a class="drawer-item" href="{{ $links['editor'] }}" target="_blank" rel="noopener">
                <svg class="i" viewBox="0 0 24 24"><use href="#i-editor"/></svg><span>Editor</span><span class="ext">↗</span>
            </a>
        @endif
        <a class="drawer-item" href="{{ route('convert') }}">
            <svg class="i" viewBox="0 0 24 24"><use href="#i-convert"/></svg><span>Convert</span>
        </a>
        @auth
            <a class="drawer-item" href="{{ route('manage.index') }}">
                <svg class="i" viewBox="0 0 24 24"><use href="#i-splats"/></svg><span>Your Splats</span>
            </a>
        @endauth
    </nav>

    <div class="drawer-footer">
        @if ($links['feedback'])
            <a class="drawer-item" href="{{ $links['feedback'] }}" target="_blank" rel="noopener">
                <svg class="i" viewBox="0 0 24 24"><use href="#i-feedback"/></svg><span>Send feedback</span><span class="ext">↗</span>
            </a>
        @endif
        @if ($links['github'])
            <a class="drawer-item" href="{{ $links['github'] }}" target="_blank" rel="noopener">
                <svg class="i" viewBox="0 0 24 24"><use href="#i-github"/></svg><span>GitHub</span><span class="ext">↗</span>
            </a>
        @endif
        @if ($links['discord'])
            <a class="drawer-item" href="{{ $links['discord'] }}" target="_blank" rel="noopener">
                <svg class="i" viewBox="0 0 24 24"><use href="#i-discord"/></svg><span>Discord</span><span class="ext">↗</span>
            </a>
        @endif

        @guest
            <a class="drawer-item" href="{{ route('register') }}">
                <svg class="i" viewBox="0 0 24 24"><use href="#i-signup"/></svg><span>Sign Up</span>
            </a>
            <a class="drawer-item" href="{{ route('login') }}">
                <svg class="i" viewBox="0 0 24 24"><use href="#i-login"/></svg><span>Login</span>
            </a>
        @endguest
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="drawer-item drawer-button">
                    <svg class="i" viewBox="0 0 24 24"><use href="#i-logout"/></svg><span>Logout ({{ auth()->user()->name }})</span>
                </button>
            </form>
        @endauth
    </div>
</aside>
