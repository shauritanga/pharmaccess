<!-- Sidebar wrapper starts -->
<nav id="sidebar" class="sidebar-wrapper">

    <!-- Sidebar profile starts -->
    <div class="sidebar-profile">
        <img src="assets/images/user6.png" class="img-shadow img-3x me-3 rounded-5" alt="Hospital Admin Templates">
        <div class="m-0">
            <h5 class="mb-1 profile-name text-nowrap text-truncate">Boniface Balele</h5>
            <p class="m-0 small profile-name text-nowrap text-truncate">Dept Admin</p>
        </div>
    </div>
    <!-- Sidebar profile ends -->

    <!-- Sidebar menu starts -->
    <div class="sidebarMenuScroll">
        <ul class="sidebar-menu">
            <li class="{{ request()->routeIs('home') ? 'active current-page' : '' }}">
                <a href="{{ route('home') }}">
                    <i class="ri-home-6-line"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <li class="treeview {{ request()->is('hospitals*') ? 'active current-page' : '' }}">
                <a href="#">
                    <i class="ri-building-2-line"></i>
                    <span class="menu-text">Hospitals</span>
                </a>
                <ul class="treeview-menu {{ request()->routeIs('hospitals') ? '' : '' }}">
                    <li>
                        <a href="{{ route('hospitals') }}" class="active-sub">Hospital List</a>
                    </li>
                    <li>
                        <a href="{{ route('add-hospitals') }}">Add Hospital</a>
                    </li>
                    <li>
                        <a href="{{ route('edit-hospitals') }}" class="active-sub">Edit Hospital</a>
                    </li>
                </ul>
            </li>
            <li class="">
                <a href="{{ route('medication') }}">
                    <i class="ri-capsule-line"></i>
                    <span class="menu-text">Medication Database</span>
                </a>
            </li>
            <li class="">
                <a href="{{ route('top-diseases') }}">
                    <i class="ri-flask-line"></i>
                    <span class="menu-text">Top Diseases</span>
                </a>
            </li>
            <li class="">
                <a href="{{ route('chronic-diseases') }}">
                    <i class="ri-heart-pulse-fill"></i>
                    <span class="menu-text">Chronic Disease</span>
                </a>
            </li>
            <li class="">
                <a href="{{ route('patients') }}">
                    <i class="ri-nurse-line"></i>
                    <span class="menu-text">Total Patients</span>
                </a>
            </li>
            <li class="">
                <a href="{{ route('settings') }}">
                    <i class="ri-settings-3-line"></i>
                    <span class="menu-text">Settings</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- Sidebar menu ends -->

    <!-- Sidebar contact starts -->
    <div class="sidebar-contact">
        <p class="fw-light mb-1 text-nowrap text-truncate">Emergency Contact</p>
        <h5 class="m-0 lh-1 text-nowrap text-truncate">+255 675 123 890</h5>
        <i class="ri-phone-line"></i>
    </div>
    <!-- Sidebar contact ends -->

</nav>
<!-- Sidebar wrapper ends -->