<nav class="navbar navbar-expand-lg navbar-light bg-faded">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" data-toggle="collapse" class="navbar-toggle d-lg-none float-right">
                <span class="sr-only">تغییر ناوبری</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <span class="d-lg-none navbar-right navbar-collapse-toggle">
                <a class="open-navbar-container">
                    <i class="ft-more-vertical"></i>
                </a>
            </span>
            <span class="font-medium-2">
                {{ Auth::user()->name }} 
                {{ Auth::user()->family }} 
            </span>
        </div>
        <div class="navbar-container">
            <div id="navbarSupportedContent" class="collapse navbar-collapse">
                <ul class="navbar-nav">
                    <li class="dropdown nav-item mr-0">
                        <a id="dropdownBasic3" href="#" data-toggle="dropdown"
                            class="nav-link position-relative dropdown-user-link dropdown-toggle"
                        >
                            <span class="avatar avatar-online">
                                <img src="{{ asset('custom/img/user-avatar-1.png') }}"
                                    alt="آواتار کاربر" style="width: 100px;"
                                >
                            </span>
                        </a>
                        {{-- <div aria-labelledby="dropdownBasic3" class="dropdown-menu dropdown-menu-left">
                            <div class="arrow_box_right">
                                @php
                                    $user = Auth::user();
                                    // $activeRole = session('active_role');
                                    $isUserRole = $activeRole === 'user';
                                    $hasMultipleCenters = $user->centers()->count() > 1;
                                    $showChangeCenter = $isUserRole && $hasMultipleCenters;
                                @endphp

                                @if($showChangeCenter)
                                    <a href="{{ route('user.select-center.index') }}" class="dropdown-item py-1">
                                        <i class="icon-home"></i>
                                        <span>انتخاب مرکز دیگر</span>
                                    </a>
                                @endif
                                
                                <form action="{{ route('logout') }}" method="POST" class="dropdown-item m-0 p-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-1">
                                        <i class="ft-power"></i>
                                        <span>خروج</span>
                                    </button>
                                </form>                                
                            </div>
                        </div> --}}

                        <div aria-labelledby="dropdownBasic3" class="dropdown-menu dropdown-menu-left">
                            <div class="arrow_box_right">  
                                @php
                                    $user = Auth::user();
                                    $hasMultipleCenters = $user->centers()->count() > 1;
                                @endphp                              
                                @if($hasMultipleCenters)
                                    <a href="{{ route('user.select-center.index') }}" class="dropdown-item py-1">
                                        <i class="icon-home"></i>
                                        <span>انتخاب مرکز دیگر</span>
                                    </a>
                                @endif
                                
                                <form action="{{ route('logout') }}" method="POST" class="dropdown-item m-0 p-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-1">
                                        <i class="ft-power"></i>
                                        <span>خروج</span>
                                    </button>
                                </form>                                
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>