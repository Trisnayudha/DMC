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
        <li class="">
            <a class="nav-link" href="{{ Route('events') }}">
                <i class="fa fa-calendar" aria-hidden="true"></i>
                <span>Events</span></a>
        </li>
        <li class="">
            <a class="nav-link" href="">
                <i class="fa fa-university" aria-hidden="true"></i>
                <span>Sponsors</span></a>
        </li>
        <li class="">
            <a href="{{ Route('payment') }}" class="nav-link">
                <i class="fa fa-credit-card"></i>
                <span>Payment</span> </a>
        </li>
        <li class="">
            <a href="" class="nav-link">
                <i class="fa fa-credit-card" aria-hidden="true"></i>
                <span>Invoice</span> </a>
        </li>
        <li class="">
            <a href="" class="nav-link">
                <i class="fa fa-user"></i>
                <span>Users</span> </a>
        </li>
        <li class="">
            <a href="" class="nav-link">
                <i class="fa fa-users" aria-hidden="true"></i>
                <span>Users Management</span> </a>
        </li>

    </ul>


</aside>
