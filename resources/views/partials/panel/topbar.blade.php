<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ auth()->user()->role->home() }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/panel/images/global/favicon.svg') }}" alt="" height="30">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('assets/panel/images/global/logo-light.svg') }}" alt="" width="150px"
                                height="auto">
                        </span>
                    </a>
                </div>

                <button type="button"
                    class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle shadow-none"
                        data-toggle="fullscreen">
                        <i class="bx bx-fullscreen fs-22"></i>
                    </button>
                </div>

                @if (auth()->user()->isAdmin())
                    <div class="ms-1 header-item d-none dropdown d-sm-flex">
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle shadow-none"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bxs-report fs-22"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Download reports</h6></li>
                            <li><a class="dropdown-item" href="{{ route('admin.reports.orders') }}">Orders</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.reports.earnings') }}">Merchant earnings</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.reports.users') }}">Accounts</a></li>
                        </ul>
                    </div>
                @endif

                <div class="ms-1 header-item d-none d-sm-flex">
                    <a href="{{ route('home') }}"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle shadow-none">
                        <i class="ri-store-2-line fs-22"></i>
                    </a>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn shadow-none" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user"
                                src="{{ auth()->user()->photo ? asset('images/'.auth()->user()->photo) : asset('assets/panel/images/global/favicon.svg') }}"
                                alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">
                                    {{ auth()->user()->full_name }}
                                </span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">
                                    {{ auth()->user()->role->label() }}
                                </span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Welcome {{ auth()->user()->firstname }}</h6>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form-topbar').submit();">
                            <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Logout</span>
                        </a>
                        <form id="logout-form-topbar" method="POST" action="{{ route('logout') }}"
                            class="d-none">@csrf</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
