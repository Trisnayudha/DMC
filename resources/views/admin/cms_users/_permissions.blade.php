<div class="card" id="team-menu-access">
    <div class="card-header"><h4><i class="fas fa-list-alt mr-2"></i>Hak Akses Menu per Level</h4></div>
    <div class="card-body">
        <p class="text-muted">Centang menu yang dapat dibuka dan dikelola setiap tim. Dashboard tersedia untuk semua level. Superadmin memiliki seluruh akses dan mengelola CMS Users.</p>
        <form method="POST" action="{{ route('admin.cms_users.permissions') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-sm table-bordered cms-permissions-table">
                    <thead><tr><th>Menu</th>@foreach ($teamRoles as $key => $label)<th class="text-center">{{ $label }}</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach ($permissionModules as $group => $modules)
                            <tr class="bg-light"><th colspan="{{ count($teamRoles) + 1 }}">{{ $group }}</th></tr>
                            @foreach ($modules as $moduleKey => $module)
                                <tr><td>{{ isset($module[5]) ? $module[5] . ' / ' : '' }}{{ $module[0] }}</td>
                                    @foreach ($teamRoles as $roleKey => $roleLabel)
                                        @php $selected = old('permissions') !== null ? old('permissions.' . $roleKey, []) : ($rolePermissions[$roleKey]->permissions ?? []); @endphp
                                        <td class="text-center"><input type="checkbox" name="permissions[{{ $roleKey }}][]" value="{{ $moduleKey }}" aria-label="{{ $roleLabel }}: {{ $module[0] }}" {{ in_array($moduleKey, $selected, true) ? 'checked' : '' }}></td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan Hak Akses</button>
        </form>
    </div>
</div>
<style>
    .cms-permissions-table td,.cms-permissions-table th { padding:8px 12px; vertical-align:middle; font-size:12px; }
    .cms-permissions-table input { width:16px; height:16px; accent-color:#6777ef; cursor:pointer; }
    .cms-permissions-table thead th { white-space:nowrap; }
</style>
