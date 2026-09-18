<ul class="my-account-nav">
    <li>
        <a href="{{ route('account') }}"
            class="my-account-nav-item {{ request()->routeIs('account') ? 'active' : '' }}">Dashboard</a>
    </li>
    <li>
        <a href="{{ route('orders.index') }}"
            class="my-account-nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">Orders</a>
    </li>
    <li>
        <a href="{{ route('logout') }}" class="my-account-nav-item"
            onclick="event.preventDefault(); document.getElementById('logout-form-account').submit();">Logout</a>
        <form id="logout-form-account" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>
    </li>
</ul>
