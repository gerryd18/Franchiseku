
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{route('adminDashboard')}}" class="logo logo-dark">
                    <span class="logo-sm">
                        {{-- <img src="{{asset('backend/assets/images/logo-sm.png')}}" alt="logo-sm" height="22"> --}}
                        <img src="{{asset('authImg/franchiseku_logo.png')}}" alt="logo-sm" height="20">
                    </span>
                    <span class="logo-lg">
                        <img src="{{asset('authImg/franchiseku_logo.png')}}" alt="logo-dark" height="20">
                        {{-- <img src="{{asset('backend/assets/images/logo-dark.png')}}" alt="logo-dark" height="20"> --}}
                    </span>
                </a>
                
                <a href="{{route('adminDashboard')}}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{asset('authImg/franchiseku_logo_light.png')}}" alt="logo-sm-light" height="22">
                        {{-- <img src="{{asset('backend/assets/images/logo-sm.png')}}" alt="logo-sm-light" height="22"> --}}
                    </span>
                    <span class="logo-lg">
                        <img src="{{asset('authImg/franchiseku_logo_light.png')}}" alt="logo-light" height="20">
                        {{-- <img src="{{asset('backend/assets/images/logo-light.png')}}" alt="logo-light" height="20"> --}}
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                <i class="ri-menu-2-line align-middle"></i>
            </button>

            <!-- App Search-->
            <form class="app-search d-none d-lg-block">
                <div class="position-relative">
                    <input type="text" class="form-control" placeholder="Search...">
                    <span class="ri-search-line"></span>
                </div>
            </form>

        </div> 

        <div class="d-flex">

            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line"></i>
                </button>
            </div>

            @php
                $id = Auth::user()->id;
                $adminData = App\Models\User::find($id);
                $profileImg = $adminData->profile_image;

                if($profileImg = ""){
                    $profileImg = "no_image.png";
                }else{
                    $profileImg = $adminData->profile_image;
                }

            @endphp

            <div class="dropdown d-inline-block user-dropdown">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   
                    <span class="d-none d-xl-inline-block ms-1">{{$adminData->name}}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="{{route('profile.edit')}}"><i class="ri-user-line align-middle me-1"></i> Profile</a>
                    <a class="dropdown-item" href="{{route('change.password')}}"><i class="ri-wallet-2-line align-middle me-1"></i> Change Password</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="{{route('admin.logout')}}"><i class="ri-shut-down-line align-middle me-1 text-danger"></i> Logout</a>
                </div>
            </div>

        </div>
    </div>
</header>