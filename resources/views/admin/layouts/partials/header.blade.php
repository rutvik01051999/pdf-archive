<header class="app-header">
    <div class="main-header-container container-fluid">
        <div class="header-content-left">
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="{{ route('admin.dashboard.index') }}" class="header-logo">
                        <img src="{{ asset('/assets/images/logo_matrix-org.gif') }}" alt="logo"
                            class="desktop-logo" style="width: 100px; height: auto;">
                        <img src="{{ asset('/assets/images/logo_matrix-org.gif') }}" alt="logo"
                            class="toggle-logo" style="width: 100px; height: auto;">
                        <img src="{{ asset('/assets/images/logo_matrix-org.gif') }}" alt="logo"
                            class="desktop-dark" style="width: 100px; height: auto;">
                        <img src="{{ asset('/assets/images/logo_matrix-org.gif') }}" alt="logo"
                            class="toggle-dark" style="width: 100px; height: auto;">
                        <img src="{{ asset('/assets/images/logo_matrix-org.gif') }}" alt="logo"
                            class="desktop-white" style="width: 100px; height: auto;">
                        <img src="{{ asset('/assets/images/logo_matrix-org.gif') }}" alt="logo"
                            class="toggle-white" style="width: 100px; height: auto;">
                    </a>
                </div>
            </div>

            <div class="header-element">
                <a aria-label="Hide Sidebar"
                    class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                    data-bs-toggle="sidebar" href="javascript:void(0);">
                    <span></span>
                </a>
            </div>
        </div>

        <div class="header-content-right">
            @include('admin.layouts.partials.localization')

            <div class="header-element header-theme-mode">
                <a href="javascript:void(0);" class="header-link layout-setting">
                    <span class="light-layout">
                        <i class="bx bx-moon header-link-icon"></i>
                    </span>
                    <span class="dark-layout">
                        <i class="bx bx-sun header-link-icon"></i>
                    </span>
                </a>
            </div>

            <div class="header-element header-fullscreen">
                <a onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                    <i class="bx bx-fullscreen full-screen-open header-link-icon"></i>
                    <i class="bx bx-exit-fullscreen full-screen-close header-link-icon d-none"></i>
                </a>

            </div>

            <div class="header-element">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="me-sm-2 me-0">
                           <img src="{{ asset('assets/images/faces/9.jpg') }}" 
     alt="img" 
     width="32" 
     height="32" 
     class="rounded-circle">
                        </div>
                        <div class="d-sm-block d-none">
                            <p class="fw-semibold mb-0 lh-1">
                                {{ auth()->user()->admin_full_name }}
                            </p>
                            <span class="op-7 fw-normal d-block fs-11">
                                
                            </span>
                        </div>
                    </div>
                </a>

                <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                    aria-labelledby="mainHeaderProfile">
                    <li>
                        <a class="dropdown-item d-flex" href="javascript:void(0);"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="ti ti-logout fs-18 me-2 op-7"></i>
                            {{ __('module.user.logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>

            <div class="header-element">
                <a href="javascript:void(0);" class="header-link switcher-icon" data-bs-toggle="offcanvas"
                    data-bs-target="#switcher-canvas">
                    <i class="bx bx-cog header-link-icon"></i>
                </a>
            </div>
        </div>
    </div>
</header>
