<div class="sidebar-content">
    <div class="nav-container">
        <ul id="main-menu-navigation" data-menu="menu-navigation"
            class="navigation navigation-main"
        >
            <li class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                <a href="{{ route('user.dashboard') }}">
                    <i class="icon-screen-desktop font-medium-4"></i>
                    <span class="menu-title">داشبورد</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('user.food-reservation.index') ? 'active' : '' }}">
                <a href="{{ route('user.food-reservation.index') }}">
                    <i class="icon-calendar font-medium-4"></i>
                    <span class="menu-title">رزرو غذا</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('user.reserves.index') ? 'active' : '' }}">
                <a href="{{ route('user.reserves.index') }}">
                    <i class="icon-list font-medium-4"></i>
                    <span class="menu-title">لیست رزروها</span>
                </a>
            </li>
            <li class="{{ request()->route('user.credit-card.index') ? 'active' : '' }}">
                <a href="{{ route('user.credit-card.index') }}">
                    <i class="icon-credit-card font-medium-4"></i>
                    <span class="menu-title">وضعیت کارت</span>
                </a>
            </li>
        </ul>
    </div>
</div>
