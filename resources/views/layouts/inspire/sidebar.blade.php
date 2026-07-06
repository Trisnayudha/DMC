<aside id="sidebar-wrapper">
    <div class="sidebar-brand" style="padding-top:2ch; padding-bottom:2ch;">
        <a href="" class="brand-link">
            <span>
                <img src="{{ asset('image/dmc.png') }}" alt="" style="border:none; width:70px;">
                <strong style="color:#000;">DMC</strong>
            </span>
        </a>
    </div>

    <div class="sidebar-brand sidebar-brand-sm">
        <a href="">DMC</a>
    </div>

    <ul class="sidebar-menu">

        {{-- ── MAIN ─────────────────────────────────── --}}
        <li>
            <a class="nav-link {{ request()->is('home') || request()->routeIs('home') ? 'active' : '' }}"
                href="{{ Route('home') }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- ── MEMBER & DATA ────────────────────────── --}}
        <li class="menu-header">Member &amp; Data</li>

        <li class="{{ request()->is('admin/member*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ Route('members') }}">
                <i class="fas fa-id-badge"></i>
                <span>Data Tampung</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/users') || request()->routeIs('users') ? 'active' : '' }}">
            <a class="nav-link" href="{{ Route('users') }}">
                <i class="fas fa-users"></i>
                <span>Members DMC</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/master-database*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.master_database.index') }}">
                <i class="fas fa-database"></i>
                <span>Master Database</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/company-database*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.company_database.index') }}">
                <i class="fas fa-building"></i>
                <span>Company Database</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/company-categories*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.company_categories.index') }}">
                <i class="fas fa-tags"></i>
                <span>Company Categories</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/company-subcategories*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.company_subcategories.index') }}">
                <i class="fas fa-sitemap"></i>
                <span>Company Subcategories</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/interview-schedule*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.interview-schedule.sponsor.index') }}">
                <i class="fas fa-microphone-alt"></i>
                <span>Interview Schedule</span>
            </a>
        </li>

        {{-- ── CONTENT ──────────────────────────────── --}}
        <li class="menu-header">Content</li>

        <li class="dropdown {{ request()->is('admin/news*') || request()->is('admin/program*') ? 'active' : '' }}">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                <i class="fas fa-newspaper"></i>
                <span>News</span>
            </a>
            <ul class="dropdown-menu">
                <li class="{{ request()->is('admin/news') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ Route('news') }}">News List</a>
                </li>
                <li class="{{ request()->is('admin/news-category*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ Route('news.category') }}">News Category</a>
                </li>
                <li class="{{ request()->is('admin/program*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('admin/program') }}">Program Article</a>
                </li>
            </ul>
        </li>

        <li class="{{ request()->is('admin/digital-edition*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ Route('digital-edition.index') }}">
                <i class="fas fa-book-open"></i>
                <span>Digital Edition</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/videos*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ Route('videos') }}">
                <i class="fas fa-video"></i>
                <span>Videos Highlight</span>
            </a>
        </li>

        <li class="dropdown {{ request()->is('admin/event*') ? 'active' : '' }}">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                <i class="fas fa-calendar-alt"></i>
                <span>Events</span>
            </a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('events') }}">Events List</a></li>
                <li><a class="nav-link" href="{{ Route('events.sponsor') }}">Sponsor Invitation</a></li>
                <li><a class="nav-link" href="{{ Route('events.category') }}">Category</a></li>
                <li><a class="nav-link" href="{{ Route('events.tickets') }}">Tickets</a></li>
                <li><a class="nav-link" href="{{ Route('events.conference') }}">Conference</a></li>
                <li><a class="nav-link" href="{{ Route('events.highlight') }}">Highlight</a></li>
                <li><a class="nav-link" href="{{ Route('events.schedule') }}">Schedule</a></li>
                <li><a class="nav-link" href="{{ Route('speakers.index') }}">Speakers</a></li>
                <li><a class="nav-link" href="{{ Route('rundown.index') }}">Rundown</a></li>
            </ul>
        </li>

        {{-- ── MARKETING & COMMUNICATION ────────────── --}}
        <li class="menu-header">Marketing &amp; Communication</li>

        <li
            class="dropdown {{ request()->is('admin/advertisement*') || request()->is('admin/marketing*') ? 'active' : '' }}">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                <i class="fas fa-bullhorn"></i>
                <span>Advertisement</span>
            </a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ url('admin/advertisement') }}">Side Banner</a></li>
                <li><a class="nav-link" href="{{ Route('marketing.ads') }}">Pop Up</a></li>
            </ul>
        </li>

        <li class="{{ request()->is('admin/email*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ Route('email.index') }}">
                <i class="fas fa-envelope"></i>
                <span>Email Management</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/notification*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ Route('notification') }}">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
        </li>

        <li class="dropdown {{ request()->is('admin/whatsapp*') ? 'active' : '' }}">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                <i class="fab fa-whatsapp"></i>
                <span>Whatsapp</span>
            </a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('campaign.index') }}">Campaign</a></li>
                <li><a class="nav-link" href="{{ Route('db.index') }}">Database</a></li>
                <li><a class="nav-link" href="{{ Route('template.index') }}">Template</a></li>
                <li><a class="nav-link" href="{{ Route('blasting.index') }}">Blasting</a></li>
                <li><a class="nav-link" href="{{ Route('sender.index') }}">Sender</a></li>
            </ul>
        </li>

        <li class="{{ request()->is('admin/sponsors*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('admin/sponsors') }}">
                <i class="fas fa-handshake"></i>
                <span>Sponsors</span>
            </a>
        </li>

        {{-- ── FINANCE & MEMBERSHIP ─────────────────── --}}
        <li class="menu-header">Finance &amp; Membership</li>

        <li class="dropdown {{ request()->is('admin/payment*') || request()->is('admin/invoice*') ? 'active' : '' }}">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                <i class="fas fa-credit-card"></i>
                <span>Payments</span>
            </a>
            <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ Route('payment') }}">Payment List</a></li>
                <li><a class="nav-link" href="#">Invoice</a></li>
            </ul>
        </li>

        <li class="dropdown {{ request()->is('admin/membership*') ? 'active' : '' }}">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                <i class="fas fa-id-card"></i>
                <span>Membership</span>
            </a>
            <ul class="dropdown-menu">
                <li class="{{ request()->is('admin/membership-tier-banners*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('membership-tier-banners.index') }}">Tier Banners</a>
                </li>
            </ul>
        </li>

        <li class="{{ request()->is('admin/scholarship*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('admin/scholarship') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Scholarship</span>
            </a>
        </li>

        {{-- ── SYSTEM ───────────────────────────────── --}}
        <li class="menu-header">System</li>

        <li class="{{ request()->is('admin/users/edit-logs*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ Route('admin.user_edit_logs') }}">
                <i class="fas fa-history"></i>
                <span>User Edit Logs</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/cms-users*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ Route('admin.cms_users.index') }}">
                <i class="fas fa-user-shield"></i>
                <span>CMS Users</span>
            </a>
        </li>

        <li class="{{ request()->is('admin/ngrok*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.ngrok.index') }}">
                <i class="fas fa-plug"></i>
                <span>Ngrok Setting</span>
            </a>
        </li>

    </ul>
</aside>
