<aside id="sidebar-wrapper ">
    <div class="sidebar-brand" style="padding-top: 2ch; padding-bottom: 2ch">
        <a href="" class=" brand-link">
            <span>
                <img src="{{ asset('image/dmc.png') }}" alt="" style="border: none; width:70px"> <strong
                    style="color: rgb(0, 0, 0)">DMC</strong>
            </span>
        </a>
    </div>
    <div style="padding-top: 5ch"></div>
    <div class="sidebar-brand sidebar-brand-sm">
        <a href="">DMC</a>
    </div>
    <ul class="sidebar-menu">
        <li class="">
            <a class="nav-link" href="{{ Route('home') }}">
                <i class="fas fa-desktop"></i>
                <span>Dashboard</span></a>
        </li>
        <li class="">
            <a class="nav-link" href="{{ Route('events-sementara') }}">
                <i class="fa fa-university" aria-hidden="true"></i>
                <span>Events sementara</span></a>
        </li>
        <li class="dropdown">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-calendar"
                    aria-hidden="true"></i>
                <span>Events</span></a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('events') }}">Events List</a></li>
                <li><a class="nav-link" href="layout-transparent.html">Events Category</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-edit"
                    aria-hidden="true"></i>
                <span>News</span></a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('news') }}">News List</a></li>
                <li><a class="nav-link" href="layout-transparent.html">News Category</a></li>
            </ul>
        </li>
        <li class="">
            <a class="nav-link" href="">
                <i class="fa fa-university" aria-hidden="true"></i>
                <span>Sponsors</span></a>
        </li>
        <li class="dropdown">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-credit-card"
                    aria-hidden="true"></i>
                <span>Payments</span></a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('payment') }}">Payment</a></li>
                <li><a class="nav-link" href="#">Invoice</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-users"
                    aria-hidden="true"></i>
                <span>Users Management</span></a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="#">Users</a></li>
                <li><a class="nav-link" href="#">Role</a></li>
            </ul>
        </li>
    </ul>


</aside>
