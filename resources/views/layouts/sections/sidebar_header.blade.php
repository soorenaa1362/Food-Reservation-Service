{{-- <div class="sidebar-header">
    <div class="logo clearfix">
        <a href="{{ auth()->check() ? route(auth()->user()->role === 'user' ? 'user.dashboard.dashboard' : (auth()->user()->role === 'admin' ? 'admin.dashboard' : 'super-admin.dashboard')) : route('login') }}" class="logo-text float-right">
            <span class="text align-middle">شرکت بهاران</span>
        </a>
    </div>
</div> --}}

<div class="sidebar-header">
    <div class="logo clearfix">
        @if (auth()->check())
            @if (auth()->user()->role === 'user')
                <a href="{{ route('user.dashboard') }}" class="logo-text float-right">
                    <span class="text align-middle">شرکت بهاران</span>
                </a>
            @elseif (auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="logo-text float-right">
                    <span class="text align-middle">شرکت بهاران</span>
                </a>
            @elseif (auth()->user()->role === 'super-admin')
                <a href="{{ route('super-admin.dashboard') }}" class="logo-text float-right">
                    <span class="text align-middle">شرکت بهاران</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="logo-text float-right">
                    <span class="text align-middle">شرکت بهاران</span>
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="logo-text float-right">
                <span class="text align-middle">شرکت بهاران</span>
            </a>
        @endif
    </div>
</div>