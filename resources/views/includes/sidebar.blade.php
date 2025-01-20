<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        {{-- <div>
            <img src="assets/images/logo-icon.png" class="logo-icon" alt="logo icon">
        </div> --}}
        <div class="row p-3">

            {{-- <img src="{{ url('assets/img/' . config('qcenv.MAIN_LOGO')) }}" class="img-fluid" alt="HRIS" /> --}}

            {{-- @if (config('app.env') != 'production')
                <h5>{{ config('app.env') }}</h5>
            @endif --}}
            <h4 class="logo-text">HRIS</h4>
        </div>
        {{-- @if (auth()->user()->group != 'manager') --}}
            <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
            </div>
        {{-- @endif --}}
    </div>

    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li class="menu-label pt-0">Menu</li>
        <li>
            <a href="{{ route('dashboard.index') }}">
                <div class="parent-icon"><i class="bx bx-home-circle"></i>
                </div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-user-pin'></i>
                </div>
                <div class="menu-title">Staff</div>
            </a>
            <ul>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>User</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Employee shifts</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Role</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Payroll</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-dollar'></i>
                </div>
                <div class="menu-title">Finance</div>
            </a>
            <ul>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Accounts</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Deposit</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Cost</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Transfer</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-briefcase'></i>
                </div>
                <div class="menu-title">Project Management</div>
            </a>
            <ul>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Projects</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Client</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Calendar</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Kanban Board</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='lni lni-users'></i>
                </div>
                <div class="menu-title">Organization</div>
            </a>
            <ul>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Company</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Location</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Department</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Announcement</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Policy</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='lni lni-timer'></i>
                </div>
                <div class="menu-title">Timesheet</div>
            </a>
            <ul>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Presence</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Update Presence</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Calendar</a>
                </li>
                <li> <a href="#"><i class="bx bx-right-arrow-alt"></i>Overtime Request</a>
                </li>
            </ul>
        </li>

        {{-- <li>
            <a href="{{ route('projects.index') }}">
                <div class="parent-icon"><i class="bx bx-category"></i>
                </div>
                <div class="menu-title">Proyek</div>
            </a>
        </li> --}}
    </ul>
    <!--end navigation-->
</div>

@push('addon-script')
    <!-- JavaScript to simulate click on toggle-icon if group is 'manager' -->
    {{-- @if (auth()->user()->group == 'manager')
        <script>
            $(document).ready(function() {
                $('.toggle-icon').click(); // Simulates a click on the toggle-icon
            });
        </script>
    @endif --}}
@endpush
