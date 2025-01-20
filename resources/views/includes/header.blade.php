<header>
    <div class="topbar d-flex align-items-center" >
        <nav class="navbar navbar-expand">
            <div class="mobile-toggle-menu">
                <i class='bx bx-menu'>
                </i>
            </div>
            <div class="ms-auto"></div>
            @yield('refresh-button')

            <div class="user-box dropdown" style="border-left: none">
                <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#"
                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{-- <img src="assets/images/avatars/avatar-2.png" class="user-img" alt="user avatar"> --}}
                    <i class='bx bx-user-circle ms-2' style="font-size: 30px">
                    </i>
                    <div class="user-info ps-2">
                        <p id="header-username" class="user-name mb-0" data-value="{{ Auth::user()?->name }}">
                            {{ Auth::user()?->first_name }} {{ Auth::user()?->last_name }}</p>
                        {{-- <p class="designattion mb-0">Web Designer</p> --}}
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    {{-- <li>
                        <a class="dropdown-item"
                            href=" {{ Auth::user()->group == 'callbacker' ? route('callbackers.change-password.index') : route('users.change-password.index') }}">
                            <i class="bx bx-key me-0"></i>
                            Ganti Password
                        </a>
                    </li> --}}
                    {{-- <li>
                        <div class="dropdown-divider mb-2"></div>
                    </li> --}}
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"><i
                                    class='bx bx-log-out-circle'></i><span>Logout</span></button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
