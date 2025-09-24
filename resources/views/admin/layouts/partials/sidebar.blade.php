<aside class="app-sidebar sticky" id="sidebar">
    <div class="main-sidebar-header" style="background: transparent !important; border: none !important;">
        <a href="{{ route('admin.dashboard.index') }}" class="header-logo" style="background: transparent !important; border: none !important; padding: 10px;">
            <img src="{{ asset('assets/images/logo_matrix-org.gif') }}" alt="logo" class="desktop-logo" style="width: 100px; height: auto; background: transparent !important;">
            <img src="{{ asset('assets/images/logo_matrix-org.gif') }}" alt="logo" class="toggle-logo" style="width: 100px; height: auto; background: transparent !important;">
            <img src="{{ asset('assets/images/logo_matrix-org.gif') }}" alt="logo" class="desktop-dark" style="width: 100px; height: auto; background: transparent !important;">
            <img src="{{ asset('assets/images/logo_matrix-org.gif') }}" alt="logo" class="toggle-dark" style="width: 100px; height: auto; background: transparent !important;">
            <img src="{{ asset('assets/images/logo_matrix-org.gif') }}" alt="logo" class="desktop-white" style="width: 100px; height: auto; background: transparent !important;">
            <img src="{{ asset('assets/images/logo_matrix-org.gif') }}" alt="logo" class="toggle-white" style="width: 100px; height: auto; background: transparent !important;">
        </a>
    </div>
    <div class="main-sidebar" id="sidebar-scroll" data-simplebar="init">
        <div class="simplebar-wrapper" style="margin: -8px 0px -80px;">
            <div class="simplebar-height-auto-observer-wrapper">
                <div class="simplebar-height-auto-observer"></div>
            </div>
            <div class="simplebar-mask">
                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                    <div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content"
                        style="height: 100%; overflow: hidden scroll;">
                        <div class="simplebar-content" style="padding: 8px 0px 80px;">
                            <nav class="main-menu-container nav nav-pills flex-column sub-open open active">
                                <div class="slide-left active d-none" id="slide-left">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                                        viewBox="0 0 24 24">
                                        <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z">
                                        </path>
                                    </svg>
                                </div>
                                <ul class="main-menu">
                                    <!-- Dashboard -->
                                    <li class="slide {{ request()->routeIs('admin.dashboard.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.dashboard.index') }}"
                                            class="side-menu__item {{ request()->routeIs('admin.dashboard.*') ? 'active' : '' }}">
                                            <i class="bx bx-home side-menu__icon"></i>
                                            <span class="side-menu__label">
                                                Dashboard
                                            </span>
                                        </a>
                                    </li>



            <!-- PDF Archive Management Section -->
            <li class="slide has-sub {{ request()->routeIs('admin.archive.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);"
                    class="side-menu__item {{ request()->routeIs('admin.archive.*') ? 'active' : '' }}">
                    <i class="bx bx-file-blank side-menu__icon"></i>
                    <span class="side-menu__label">
                        PDF Archive
                    </span>
                    <i class="fe fe-chevron-right side-menu__angle"></i>
                </a>
                <ul class="slide-menu child1 {{ request()->routeIs('admin.archive.*') ? 'active' : '' }}"
                    data-popper-placement="bottom" style="{{ request()->routeIs('admin.archive.*') ? 'display: block;' : '' }}">
                    <li class="slide side-menu__label1">
                        <a href="javascript:void(0)">
                            PDF Archive Management
                        </a>
                    </li>
                    <li class="slide {{ request()->routeIs('admin.archive.upload') ? 'active' : '' }}">
                        <a href="{{ route('admin.archive.upload') }}"
                            class="side-menu__item {{ request()->routeIs('admin.archive.upload') ? 'active' : '' }}">
                            Archive Upload
                        </a>
                    </li>
                    <li class="slide {{ request()->routeIs('admin.archive.display') ? 'active' : '' }}">
                        <a href="{{ route('admin.archive.display') }}"
                            class="side-menu__item {{ request()->routeIs('admin.archive.display') ? 'active' : '' }}">
                            Archive Display
                        </a>
                    </li>
                    <li class="slide {{ request()->routeIs('admin.archive.categories') ? 'active' : '' }}">
                        <a href="{{ route('admin.archive.categories') }}"
                            class="side-menu__item {{ request()->routeIs('admin.archive.categories') ? 'active' : '' }}">
                            Categories
                        </a>
                    </li>
                    <li class="slide {{ request()->routeIs('admin.archive.special-dates') ? 'active' : '' }}">
                        <a href="{{ route('admin.archive.special-dates') }}"
                            class="side-menu__item {{ request()->routeIs('admin.archive.special-dates') ? 'active' : '' }}">
                            Special Dates
                        </a>
                    </li>
                </ul>
            </li>

                                </ul>

                                <div class="slide-right d-none" id="slide-right">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z">
                                        </path>
                                    </svg>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="simplebar-placeholder" style="width: auto; height: 1522px;"></div>
        </div>
        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
            <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
        </div>
        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
            <div class="simplebar-scrollbar"
                style="height: 570px; transform: translate3d(0px, 0px, 0px); display: block;"></div>
        </div>
    </div>
</aside>
