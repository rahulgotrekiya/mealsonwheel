<div class="tf-toolbar-bottom type-1150">
    <div class="toolbar-item">
        <a href="{{ url('/shop') }}">
            <div class="toolbar-icon"><i class="icon-shop"></i></div>
            <div class="toolbar-label">Shop</div>
        </a>
    </div>
    <div class="toolbar-item">
        <a href="#mobileMenu" data-bs-toggle="offcanvas" aria-controls="offcanvasLeft">
            <div class="toolbar-icon"><i class="icon-search"></i></div>
            <div class="toolbar-label">Search</div>
        </a>
    </div>
    <div class="toolbar-item">
        <a href="{{ auth()->check() ? url('/account') : route('login') }}">
            <div class="toolbar-icon"><i class="icon-account"></i></div>
            <div class="toolbar-label">Account</div>
        </a>
    </div>
    <div class="toolbar-item">
        <a href="{{ url('/cart') }}">
            <div class="toolbar-icon">
                <i class="icon-bag"></i>
                <div class="toolbar-count cart_count"></div>
            </div>
            <div class="toolbar-label">Cart</div>
        </a>
    </div>
</div>
