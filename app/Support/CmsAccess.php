<?php

namespace App\Support;

use App\Models\CmsUser;
use Illuminate\Routing\Route;

class CmsAccess
{
    public static function modules(): array
    {
        return config('cms_access.modules', []);
    }

    public static function configurableModules(): array
    {
        return array_diff_key(self::modules(), array_flip(['dashboard', 'cms_users']));
    }

    public static function allows(CmsUser $user, string $module): bool
    {
        if (!$user->is_active || !isset(CmsUser::ROLES[$user->role]) || !isset(self::modules()[$module])) {
            return false;
        }
        if ($user->isSuperadmin()) {
            return true;
        }
        if ($module === 'cms_users') {
            return false;
        }
        return $module === 'dashboard' || in_array($module, $user->cmsRole->permissions ?? [], true);
    }

    public static function moduleForRoute(?Route $route): ?string
    {
        if (!$route) {
            return null;
        }
        $action = $route->getAction('controller') ?? '';
        $parts = explode('@', $action, 2);
        $controller = class_basename($parts[0]);
        $shortAction = $controller . (isset($parts[1]) ? '@' . $parts[1] : '');
        if (isset(config('cms_access.actions', [])[$shortAction])) {
            return config('cms_access.actions')[$shortAction];
        }
        foreach (self::modules() as $key => $module) {
            if (in_array($controller, $module[4], true)) {
                return $key;
            }
        }
        return null;
    }

    public static function allowsRoute(CmsUser $user, ?Route $route): bool
    {
        if (!$user->is_active || !isset(CmsUser::ROLES[$user->role])) {
            return false;
        }
        if ($user->isSuperadmin()) {
            return true;
        }
        $controller = $route ? class_basename(explode('@', $route->getAction('controller') ?? '')[0]) : '';
        if (in_array($controller, ['ImageUploadController', 'UploadController', 'EditorUploadController'], true)) {
            foreach (config('cms_access.upload_modules') as $module) {
                if (self::allows($user, $module)) {
                    return true;
                }
            }
            return false;
        }
        $module = self::moduleForRoute($route);
        if ($module !== null && self::allows($user, $module)) {
            return true;
        }
        $parts = explode('@', $route ? ($route->getAction('controller') ?? '') : '', 2);
        $shortAction = class_basename($parts[0]) . (isset($parts[1]) ? '@' . $parts[1] : '');
        foreach (config('cms_access.shared_actions.' . $shortAction, []) as $shared) {
            if (self::allows($user, $shared)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Dropdown dari cms_access.submenus. Tiap link memakai izin modulnya sendiri
     * (default: modul induk) dan disembunyikan kalau tidak diizinkan; child di-highlight
     * per path (exact) karena beberapa link bisa berbagi module key. Kalau cuma tersisa
     * satu link, tampil sebagai menu langsung dengan label modul link itu.
     */
    private static function submenuItem(CmsUser $user, string $key, string $label, string $icon, array $links): ?array
    {
        $children = [];
        foreach ($links as $link) {
            $module = $link[2] ?? $key;
            if (self::allows($user, $module)) {
                $children[] = ['key' => $module, 'label' => $link[0], 'path' => $link[1], 'icon' => $icon, 'exact' => true];
            }
        }
        if (!$children) {
            return null;
        }
        if (count($children) === 1) {
            $only = $children[0];
            unset($only['exact']);
            $only['label'] = self::modules()[$only['key']][0];
            return $only;
        }
        return ['label' => $label, 'icon' => $icon, 'children' => $children];
    }

    /** Navigation, dashboard shortcuts and role checklists share the same menu definitions. */
    public static function navigation(CmsUser $user): array
    {
        $layout = config('cms_access.team_menus.' . $user->role);
        if (is_array($layout)) {
            return self::teamMenuNavigation($user, $layout);
        }
        $groups = [];
        // Modul yang tampil sebagai link di dalam submenu modul lain tidak dirender sendiri.
        $nested = [];
        foreach (config('cms_access.submenus', []) as $parent => $links) {
            foreach ($links as $link) {
                if (isset($link[2]) && $link[2] !== $parent) {
                    $nested[$link[2]] = true;
                }
            }
        }
        foreach (self::modules() as $key => $module) {
            if ($key === 'quick_search' || isset($nested[$key])) {
                continue;
            }
            [$label, $group, $path, $icon] = $module;
            $submenus = config('cms_access.submenus.' . $key);
            if ($submenus) {
                $item = self::submenuItem($user, $key, $label, $icon, $submenus);
                if ($item) {
                    $groups[$group][$key] = $item;
                }
                continue;
            }
            if (!self::allows($user, $key)) {
                continue;
            }
            $item = compact('key', 'label', 'path', 'icon');
            if (isset($module[5])) {
                $groups[$group][$module[5]]['label'] = $module[5];
                $groups[$group][$module[5]]['icon'] = $groups[$group][$module[5]]['icon'] ?? $icon;
                $groups[$group][$module[5]]['children'][] = $item;
            } else {
                $groups[$group][$key] = $item;
            }
        }
        // When a team can only access the event list, show it as one direct menu.
        if (isset($groups['Event Team']['Events']['children']) && count($groups['Event Team']['Events']['children']) === 1
            && $groups['Event Team']['Events']['children'][0]['key'] === 'events') {
            $event = $groups['Event Team']['Events']['children'][0];
            $event['label'] = 'Events';
            $groups['Event Team']['Events'] = $event;
        }
        // Team menus follow the agreed order instead of jumping between the
        // Superadmin's per-team groups, split into the sub-categories from
        // cms_access.sections. Custom grants remain visible.
        $preferred = config('cms_access.team_defaults.' . $user->role);
        if (is_array($preferred)) {
            $rank = array_flip($preferred);
            $sectionOf = [];
            foreach (config('cms_access.sections', []) as $section => $modules) {
                foreach ($modules as $module) {
                    $sectionOf[$module] = $section;
                }
            }
            $items = [];
            foreach ($groups as $group => $entries) {
                if ($group === 'Main') {
                    continue;
                }
                foreach ($entries as $key => $item) {
                    $keys = isset($item['children']) ? array_column($item['children'], 'key') : [$item['key']];
                    $positions = array_map(function ($module) use ($rank) {
                        return $rank[$module] ?? PHP_INT_MAX;
                    }, $keys);
                    $items[] = [
                        'key' => $key, 'item' => $item, 'rank' => min($positions), 'order' => count($items),
                        'section' => $sectionOf[$keys[0]] ?? 'Lainnya',
                    ];
                }
            }
            usort($items, function ($a, $b) {
                return ($a['rank'] <=> $b['rank']) ?: ($a['order'] <=> $b['order']);
            });
            $ordered = isset($groups['Main']) ? ['Main' => $groups['Main']] : [];
            foreach ($items as $entry) {
                $ordered[$entry['section']][$entry['key']] = $entry['item'];
            }
            return $ordered;
        }
        return $groups;
    }

    /** Sidebar dari cms_access.team_menus: header per menu, link langsung per sub menu. */
    private static function teamMenuNavigation(CmsUser $user, array $layout): array
    {
        $modules = self::modules();
        [$label, , $path, $icon] = $modules['dashboard'];
        $nav = ['Main' => [['key' => 'dashboard', 'label' => $label, 'path' => $path, 'icon' => $icon]]];
        foreach ($layout as $menu => $links) {
            foreach ($links as $link) {
                if (self::allows($user, $link[2])) {
                    $nav[$menu][] = ['key' => $link[2], 'label' => $link[0], 'path' => $link[1], 'icon' => $link[3] ?? $modules[$link[2]][3]];
                }
            }
        }
        return self::markActiveLink($nav);
    }

    /**
     * Tandai tepat satu link aktif. Path + query string harus cocok (link dengan query
     * paling spesifik menang, jadi "New Member" mengalahkan "Members"); kalau tidak ada
     * yang cocok (mis. halaman detail), pakai link pertama dari modul halaman ini.
     */
    private static function markActiveLink(array $nav): array
    {
        $request = request();
        $currentModule = self::moduleForRoute($request->route());
        $parentPath = config('cms_access.menu_parents.' . $currentModule);
        $best = null;
        $bestScore = -1;
        $fallback = null;
        foreach ($nav as $group => $items) {
            foreach ($items as $i => $item) {
                $nav[$group][$i]['active'] = false;
                $isFallback = $parentPath ? $item['path'] === $parentPath : $item['key'] === $currentModule;
                if ($fallback === null && $isFallback) {
                    $fallback = [$group, $i];
                }
                $parts = explode('?', $item['path'], 2);
                if (!$request->is('admin/' . $parts[0])) {
                    continue;
                }
                parse_str($parts[1] ?? '', $query);
                foreach ($query as $name => $value) {
                    if ((string) $request->query($name) !== (string) $value) {
                        continue 2;
                    }
                }
                if (count($query) > $bestScore) {
                    $best = [$group, $i];
                    $bestScore = count($query);
                }
            }
        }
        $active = $best ?? $fallback;
        if ($active) {
            $nav[$active[0]][$active[1]]['active'] = true;
        }
        return $nav;
    }
}
