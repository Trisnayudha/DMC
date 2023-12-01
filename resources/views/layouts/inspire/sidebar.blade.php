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
        {{-- <li class="">
            <a class="nav-link" href="{{ Route('special-event') }}">
                <i class="fa fa-university" aria-hidden="true"></i>
                <span>Events Special</span></a>
        </li> --}}
        <li class="">
            <a class="nav-link" href="{{ Route('members') }}">
                <i class="fa fa-user" aria-hidden="true"></i>
                <span>Members DMC</span></a>
        </li>
        <li class="">
            <a class="nav-link" href="{{ Route('videos') }}">
                <i class="fa fa-university" aria-hidden="true"></i>
                <span>Videos Highlight</span></a>
        </li>
        <li class="dropdown">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-paper-plane"
                    aria-hidden="true"></i>
                <span>Advertisement</span></a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ url('admin/advertisement') }}"> Side Banner</a></li>
                <li><a class="nav-link" href="{{ Route('marketing.ads') }}"> Pop Up</a></li>
            </ul>
        </li>
        <li class="">
            <a class="nav-link" href="{{ Route('notification') }}">
                <i class="fa fa-bell" aria-hidden="true"></i>
                <span>Notifications</span></a>
        </li>
        <li class="dropdown">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-calendar"
                    aria-hidden="true"></i>
                <span>Events</span></a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('events') }}">Events List</a></li>
                <li><a class="nav-link" href="{{ Route('events.sponsor') }}">Events Sponsor Invitation</a></li>
                <li><a class="nav-link" href="{{ Route('events.category') }}">Events Category</a></li>
                <li><a class="nav-link" href="{{ Route('events.tickets') }}">Events Tickets</a></li>
                <li><a class="nav-link" href="{{ Route('events.conference') }}">Events Conference</a></li>
                <li><a class="nav-link" href="{{ Route('events.highlight') }}">Events Highlight</a></li>
                <li><a class="nav-link" href="{{ Route('events.schedule') }}">Events Schedule</a></li>
                <li><a class="nav-link" href="{{ Route('speakers.index') }}">Events Speakers</a></li>
                <li><a class="nav-link" href="{{ Route('rundown.index') }}">Events Rundown</a></li>

            </ul>
        </li>
        <li class="dropdown">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-edit"
                    aria-hidden="true"></i>
                <span>News</span></a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('news') }}">News List</a></li>
                <li><a class="nav-link" href="{{ Route('news.category') }}">News Category</a></li>
            </ul>
        </li>
        <li class="">
            <a class="nav-link" href="{{ url('admin/sponsors') }}">
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
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><svg
                    xmlns="http://www.w3.org/2000/svg" height="1em"
                    viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                    <path
                        d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                </svg>
                <span style="padding-left: 28px">Whatsapp </span></a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('campaign.index') }}">Whatsapp Campaign</a></li>
                <li><a class="nav-link" href="{{ Route('db.index') }}">Whatsapp Database</a></li>
                <li><a class="nav-link" href="{{ Route('template.index') }}">Whatsapp Template</a></li>
                <li><a class="nav-link" href="{{ Route('blasting.index') }}">Whatsapp Blasting</a></li>
                <li><a class="nav-link" href="{{ Route('sender.index') }}">Whatsapp Sender</a></li>

            </ul>
        </li>
        <li class="dropdown">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-users"
                    aria-hidden="true"></i>
                <span>Users Management</span></a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('users') }}">Users</a></li>
                <li><a class="nav-link" href="#">Role</a></li>
            </ul>
        </li>
    </ul>


</aside>
