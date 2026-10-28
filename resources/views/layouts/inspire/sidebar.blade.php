@php
    $cmsNavigation = \App\Support\CmsAccess::navigation(auth()->user());
    $activeModule = \App\Support\CmsAccess::moduleForRoute(request()->route());
@endphp
<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        <a href="{{ route('home') }}" class="brand-link" aria-label="DMC Dashboard">
            <img src="{{ asset('image/dmc.png') }}" alt="Djakarta Mining Club"> <strong>DMC</strong>
        </a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm"><a href="{{ route('home') }}" aria-label="DMC Dashboard">DMC</a></div>

    <div class="cms-menu-search hide-sidebar-mini" role="search">
        <label class="cms-menu-search-field" for="cms-menu-search-input">
            <i class="fas fa-search" aria-hidden="true"></i>
            <span class="sr-only">Cari menu</span>
            <input type="search" id="cms-menu-search-input" placeholder="Cari menu..." autocomplete="off" aria-controls="cms-sidebar-menu" aria-describedby="cms-menu-search-status">
        </label>
        <span id="cms-menu-search-status" class="sr-only" role="status" aria-live="polite"></span>
    </div>

    <nav aria-label="Menu CMS">
        <ul class="sidebar-menu" id="cms-sidebar-menu">
            @foreach ($cmsNavigation as $group => $items)
                @php $groupId = 'cms-group-' . $loop->index; @endphp
                @if ($group !== 'Main')
                    <li class="menu-header" data-menu-group="{{ $groupId }}">{{ $group }}</li>
                @endif
                @foreach ($items as $item)
                    @if (isset($item['children']))
                        @php $parentActive = in_array($activeModule, array_column($item['children'], 'key'), true); @endphp
                        <li class="dropdown cms-menu-item {{ $parentActive ? 'active' : '' }}" data-menu-group="{{ $groupId }}" data-menu-label="{{ $item['label'] }}">
                            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" aria-label="{{ $item['label'] }}"><i class="fas fa-{{ $item['icon'] }}" aria-hidden="true"></i><span>{{ $item['label'] }}</span></a>
                            <ul class="dropdown-menu">
                                @foreach ($item['children'] as $child)
                                    @php $childActive = !empty($child['exact']) ? request()->is('admin/' . $child['path']) : $activeModule === $child['key']; @endphp
                                    <li class="cms-submenu-item {{ $childActive ? 'active' : '' }}" data-menu-label="{{ $child['label'] }}">
                                        <a class="nav-link" href="{{ url('admin/' . $child['path']) }}" @if ($childActive) aria-current="page" @endif>{{ $child['label'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="cms-menu-item {{ $activeModule === $item['key'] ? 'active' : '' }}" data-menu-group="{{ $groupId }}" data-menu-label="{{ $item['label'] }}">
                            <a class="nav-link" href="{{ url('admin/' . $item['path']) }}" aria-label="{{ $item['label'] }}" @if ($activeModule === $item['key']) aria-current="page" @endif><i class="fas fa-{{ $item['icon'] }}" aria-hidden="true"></i><span>{{ $item['label'] }}</span></a>
                        </li>
                    @endif
                @endforeach
            @endforeach
        </ul>
        <p class="cms-menu-empty hide-sidebar-mini" id="cms-menu-empty" hidden>Menu tidak ditemukan.</p>
    </nav>
</aside>
