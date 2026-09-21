<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ auth()->user()->role->home() }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/panel/images/global/favicon.svg') }}" alt="" height="30">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/panel/images/global/logo-light.svg') }}" alt="" width="150px" height="auto">
            </span>
        </a>
        <a href="{{ auth()->user()->role->home() }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/panel/images/global/favicon.svg') }}" alt="" height="30">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/panel/images/global/logo-light.svg') }}" alt="" width="150px" height="auto">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <ul class="navbar-nav" id="navbar-nav">
                @foreach ($panelMenu as $item)
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                            href="{{ route($item['route']) }}">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            @if (! empty($item['badge']))
                                <span class="badge bg-danger ms-auto">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('home') }}">
                        <i class="ri-store-2-line"></i><span>Visit Store</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form-panel').submit();">
                        <i class="ri-login-circle-line"></i><span>Logout</span>
                    </a>
                    <form id="logout-form-panel" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>
                </li>
            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>
