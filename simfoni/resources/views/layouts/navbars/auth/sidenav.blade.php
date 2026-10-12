<style>
    #sidenav-collapse-main {
        height: 80vh
    }
</style>
<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('home') }}" target="_blank">
            <img src="{{ asset('img/1.png') }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">Simfoni</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">

            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'home' ? 'active' : '' }}" href="{{ route('home') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            {{-- Menu khusus level 1 --}}
            @if(Auth::user()->level == 1)
            <!-- <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'profile' ? 'active' : '' }}" href="{{ route('profile') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profil</span>
                </a>
            </li> -->
              <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'user.index' ? 'active' : '' }}" href="{{ route('user.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-laptop text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Kelola User</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'arc') == true ? 'active' : '' }}" href="{{ route('page', ['page' => 'arc']) }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Input ARC</span>
                </a>
            </li>
            @endif

            {{-- Menu khusus level 3 --}}
            @if(Auth::user()->level == 3 || Auth::user()->level == 1)
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'fenomena.index' ? 'active' : '' }}" href="{{ route('page', ['page' => 'fenomena']) }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-fat-add text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Input Fenomena</span>
                </a>
            </li>
            @endif

            {{-- Menu untuk level 1, 2, dan 3 --}}
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'pencarian') == true ? 'active' : '' }}" href="{{ route('page', ['page' => 'pencarian']) }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-zoom-split-in text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Pencarian Fenomena</span>
                </a>
            </li>

            {{-- Menu khusus level 1 dan 2 --}}
            @if(Auth::user()->level <= 2)
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'approval') == true ? 'active' : '' }}" href="{{ route('page', ['page' => 'approval']) }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-check-bold text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Approval Fenomena</span>
                </a>
            </li>
            @endif

            @if(Auth::user()->level == 1)
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'webscrapping.index' ? 'active' : '' }}" href="{{ route('webscrapping.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-app text-info text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Web Scraping</span>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'summarize.index' ? 'active' : '' }}" href="{{ route('page', ['page' => 'summarize']) }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-world-2 text-danger text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Rangkuman Otomatis</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'fenomena.export' ? 'active' : '' }}" href="{{ route('fenomena.export') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-credit-card text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Export Fenomena</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
