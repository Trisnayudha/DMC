<?php

namespace App\Support;

use App\Models\CmsUserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CmsAudit
{
    // The same permission can own several sidebar pages, including filtered member lists.
    private static function pages(): array
    {
        $pages = [];
        foreach (CmsAccess::modules() as $key => [$label, , $path]) {
            $pages[$key] = [$key, $path, $label];
        }
        $add = static function (array $link, string $module) use (&$pages) {
            [$label, $path] = $link;
            if ($path !== $pages[$module][1]) {
                $key = preg_replace('/[^a-z0-9]+/i', '_', $path);
                $pages[$key] = [$module, $path, $label];
            }
        };
        foreach (config('cms_access.submenus', []) as $parent => $links) {
            foreach ($links as $link) {
                $add($link, $link[2] ?? $parent);
            }
        }
        foreach (config('cms_access.team_menus', []) as $groups) {
            foreach ($groups as $links) {
                foreach ($links as $link) {
                    foreach ($link['children'] ?? [$link] as $child) {
                        $add($child, $child[2]);
                    }
                }
            }
        }
        return $pages;
    }

    public static function menuLabels(): array
    {
        $labels = ['session' => 'CMS session', 'uploads' => 'Shared uploads', 'system_tools' => 'System tools'];
        foreach (self::pages() as $key => [, , $label]) {
            $labels[$key] = $label;
        }
        $labels['member_intake'] = 'Member Intake';
        asort($labels, SORT_NATURAL | SORT_FLAG_CASE);
        return $labels;
    }

    private static function matchPage(Request $request, array $modules, array $pages): ?string
    {
        $best = null;
        $score = -1;
        foreach ($pages as $key => [$module, $url]) {
            if (!in_array($module, $modules, true)) {
                continue;
            }
            [$path, $query] = array_pad(explode('?', $url, 2), 2, '');
            $path = 'admin/' . $path;
            if (!$request->is($path, $path . '/*')) {
                continue;
            }
            parse_str($query, $required);
            foreach ($required as $name => $value) {
                if (($request->query->all()[$name] ?? null) !== $value) {
                    continue 2;
                }
            }
            if (strlen($url) > $score) {
                $best = $key;
                $score = strlen($url);
            }
        }
        return $best;
    }

    public static function menu(Request $request): string
    {
        if ($request->routeIs('cms.login.submit', 'cms.logout')) {
            return 'session';
        }
        $route = $request->route();
        $controller = class_basename(Str::before($route->getActionName(), '@'));
        $upload = in_array($controller, ['ImageUploadController', 'UploadController', 'EditorUploadController'], true);
        $owner = CmsAccess::moduleForRoute($route) ?? ($upload ? 'uploads' : 'system_tools');
        $pages = self::pages();
        $menu = self::matchPage($request, [$owner], $pages) ?? $owner;
        // A shared endpoint can inherit its originating menu only from a configured caller.
        $shared = $upload ? config('cms_access.upload_modules') :
            config('cms_access.shared_actions.' . $controller . '@' . $route->getActionMethod(), []);
        $referer = $request->headers->get('referer', '');
        if (($shared || !$request->isMethodSafe() || $request->ajax() || $request->wantsJson())
            && Str::startsWith($referer, $request->getSchemeAndHttpHost() . '/')) {
            $origin = self::matchPage(Request::create($referer), array_merge([$owner], $shared), $pages);
            if ($origin && ($menu === $owner || in_array($pages[$origin][0], $shared, true))) {
                return $origin;
            }
        }
        return $menu;
    }

    public static function action(Request $request, string $menu): string
    {
        $route = $request->route();
        $operation = strtolower($route->getActionMethod());
        if ($menu === 'session') {
            return $request->routeIs('cms.logout') ? 'logout' : 'login';
        }
        if ($request->isMethod('DELETE') || Str::startsWith($operation, ['destroy', 'delete', 'remove'])) {
            return 'delete';
        }
        if (Str::contains($operation, 'import')) {
            return 'import';
        }
        if (Str::contains($operation, ['export', 'download']) || $request->routeIs('sponsors.renewal-form')) {
            return 'export';
        }
        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || Str::startsWith($operation, 'update')) {
            return 'update';
        }
        if (!$request->isMethodSafe()) {
            if (Str::startsWith($operation, ['edit', 'upsert'])) {
                return 'update';
            }
            return Str::startsWith($operation, ['store', 'create', 'add']) ? 'create' : 'action';
        }
        if (Str::startsWith($operation, 'edit') && !Str::endsWith($operation, 'logs')) {
            return 'open_edit';
        }
        if ($operation === 'create') {
            return 'open_create';
        }
        if ($request->ajax() || $request->wantsJson()) {
            return 'load';
        }
        $path = self::pages()[$menu][1] ?? null;
        return $path !== null && $request->is('admin/' . Str::before($path, '?')) ? 'menu' : 'view';
    }

    // Keep only field names and numeric record references. Never persist request values,
    // filenames, free-text search, payment codes, session tokens or full URLs.
    public static function safeField($name): bool
    {
        return is_string($name) && preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/D', $name)
            && !preg_match('/password|passwd|secret|token|credential|authorization|api_?key|signature|cvv|card_?number/i', $name);
    }

    public static function fields(Request $request): array
    {
        if ($request->isMethodSafe()) {
            return [];
        }
        $input = $request->isJson() ? $request->json() : $request->request;
        return array_slice(array_values(array_filter(array_unique(array_merge(
            $input->keys(), $request->files->keys()
        )), [self::class, 'safeField'])), 0, 60);
    }

    public static function recordIds(Request $request): array
    {
        $ids = [];
        $parameters = $request->route()->originalParameters();
        foreach (['id', 'user_id', 'event_id', 'company_id', 'payment_id', 'booking_id', 'delegate_id'] as $key) {
            $parameters += [$key => $request->input($key)];
        }
        foreach ($parameters as $key => $value) {
            if ((preg_match('/^(id|[a-z]+(?:_[a-z]+)*_id|[a-zA-Z]+Id)$/D', $key)
                || in_array($key, ['sponsor', 'renewal', 'benefitUsage', 'eventSurvey', 'memberSurvey', 'admin'], true))
                && is_scalar($value)
                && preg_match('/^[0-9]{1,20}$/D', (string) $value)) {
                $ids[$key] = (string) $value;
            }
        }
        return $ids;
    }

    public static function record(array $entry, ?CmsUserLog $log = null): ?CmsUserLog
    {
        try {
            $log = $log ?? new CmsUserLog();
            $log->fill($entry)->save();
            return $log;
        } catch (\Illuminate\Database\QueryException $exception) {
            // Keep a fallback trail without copying the SQL or the original request.
            Log::error('CMS audit database write failed', $entry);
            return null;
        }
    }
}
