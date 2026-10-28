# CMS user levels and menu access

CMS Users supports Member Team, Event Team, Marketing Team, Secretariat Team, Financial Team, and Superadmin.
Superadmins assign a level when creating or editing CMS accounts and select each
team's available modules in **CMS Users → Hak Akses Menu per Level**. A selected
module permits both its pages and actions. The setting applies to every account
with that level, on subsequent requests. Dashboard is always available; CMS Users
is reserved for Superadmin. Quick Search grants access to member and event history.

## Migration

Run these migrations before using these code changes:

```sh
php artisan migrate --path=database/migrations/2026_10_28_100000_add_roles_and_menu_permissions_to_cms_users.php
php artisan migrate --path=database/migrations/2026_10_28_110000_add_member_team_cms_role.php
```

Existing CMS accounts become Superadmin, preserving their former full access.
Assign their intended team levels from CMS Users afterward. Team permissions start with the defaults in `config/cms_access.php`, and Superadmin can adjust them. Seeded initial
admin accounts explicitly use Superadmin; ordinary new accounts default to Event Team.

CMS authentication checks permissions server-side, including direct requests and
exports. The sidebar and team dashboard use the same definitions. Team dashboards
show permitted shortcuts instead of the unrestricted cross-module KPI dashboard.
Users cannot deactivate/delete their own accounts or change their own level. The
last active Superadmin is protected from removal or demotion.

## Maintaining route coverage

`config/cms_access.php` maps controllers and action exceptions to modules.
When adding a controller/route, map it to a module explicitly. Unmapped CMS routes
are accessible only to Superadmin. Shared editor uploads require a content module.
Public registration and member API authentication are separate from CMS levels.

Run `vendor/bin/phpunit tests/Feature/CmsAccessControlTest.php` to check permissions,
route coverage, sidebar links, team assignment, migration behavior and account protection.

To explicitly replace Member, Marketing, Secretariat and Financial team permissions with the configured defaults, run `php artisan cms:apply-team-defaults --force`. This leaves Event Team and individual user assignments unchanged.
