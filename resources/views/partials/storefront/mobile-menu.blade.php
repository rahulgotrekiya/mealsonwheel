{{--
    The signed-in and signed-out states share one structure here. In the source
    markup each branch closed a different number of elements, which left the
    offcanvas panel unbalanced depending on who was looking at it.
--}}
<div class="offcanvas offcanvas-start canvas-mb" id="mobileMenu">
    <span class="icon-close icon-close-popup" data-bs-dismiss="offcanvas" aria-label="Close"></span>

    <div class="mb-canvas-content">
        <div class="mb-body">
            <ul class="nav-ul-mb" id="wrapper-menu-navigation">
                <li class="nav-mb-item">
                    <a href="{{ route('home') }}" class="mb-menu-link"><span>Home</span></a>
                </li>
                <li class="nav-mb-item">
                    <a href="#dropdown-menu-categories" class="collapsed mb-menu-link current"
                        data-bs-toggle="collapse" aria-expanded="true" aria-controls="dropdown-menu-categories">
                        <span>Categories</span>
                        <span class="btn-open-sub"></span>
                    </a>
                    <div id="dropdown-menu-categories" class="collapse">
                        <div class="toolbar-shop-mobile">
                            @include('partials.storefront.category-links')
                        </div>
                    </div>
                </li>
                <li class="nav-mb-item">
                    <a href="{{ route('shop') }}" class="mb-menu-link"><span>Shop</span></a>
                </li>
                <li class="nav-mb-item">
                    <a href="{{ route('about') }}" class="mb-menu-link"><span>About Us</span></a>
                </li>
                <li class="nav-mb-item">
                    <a href="{{ route('contact') }}" class="mb-menu-link"><span>Contact Us</span></a>
                </li>
            </ul>

            <div class="mb-other-content">
                <form class="tf-mini-search-frm mb-2 mt-3" method="GET" action="{{ route('search') }}">
                    <fieldset class="text">
                        <input type="text" placeholder="Search" name="keyword" tabindex="0"
                            value="{{ request('keyword') }}" aria-required="true" required>
                    </fieldset>
                    <button type="submit"><i class="icon-search"></i></button>
                </form>

                <div class="mb-notice">
                    <a href="{{ route('contact') }}" class="text-need">Need help ?</a>
                </div>

                <ul class="mb-info">
                    <li>
                        <p>Address: B P College Of Computer Studies, Gandhinagar</p>
                    </li>
                    <li>
                        <p>Email: <a href="mailto:support@mealsonwheels.com">support@mealsonwheels.com</a></p>
                    </li>
                    <li>
                        <p>Phone: <a href="tel:+919123654780">9123654780</a></p>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mb-bottom">
            @auth
                <a href="{{ route('logout') }}" class="site-nav-icon"
                    onclick="event.preventDefault(); document.getElementById('logout-form-mb').submit();">Logout</a>
                <form id="logout-form-mb" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>
            @else
                <a href="{{ route('login') }}" class="site-nav-icon"><i class="icon icon-account"></i>Login</a>
            @endauth
        </div>
    </div>
</div>
