<?php

namespace Database\Seeders;

use App\Models\Roles\PermissionDetail;
use App\Models\Roles\PermissionGroup;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'group_name' => 'Pengaturan Master Data Financial',
                'permission_attribute' => [
                    [
                        'name' => 'category-list',
                        'alias' => 'Halaman Utama Financial'
                    ],
                    [
                        'name' => 'category-create',
                        'alias' => 'Tambah Financial'
                    ],
                    [
                        'name' => 'category-edit',
                        'alias' => 'Edit Financial'
                    ],
                    [
                        'name' => 'category-delete',
                        'alias' => 'Hapus Financial'
                    ],
                ]
            ],
            [
                'group_name' => 'Pengaturan Master Data Production & Lifting',
                'permission_attribute' => [
                    [
                        'name' => 'skpd-list',
                        'alias' => 'Halaman Utama Production & Lifting'
                    ],
                    [
                        'name' => 'skpd-create',
                        'alias' => 'Tambah Production & Lifting'
                    ],
                    [
                        'name' => 'skpd-edit',
                        'alias' => 'Edit Production & Lifting'
                    ],
                    [
                        'name' => 'skpd-delete',
                        'alias' => 'Hapus Production & Lifting'
                    ],
                ]
            ],
            [
                'group_name' => 'Pengaturan Master Data HSSE',
                'permission_attribute' => [
                    [
                        'name' => 'requirement-list',
                        'alias' => 'Halaman Utama HSSE'
                    ],
                    [
                        'name' => 'requirement-create',
                        'alias' => 'Tambah HSSE'
                    ],
                    [
                        'name' => 'requirement-edit',
                        'alias' => 'Edit HSSE'
                    ],
                    [
                        'name' => 'requirement-delete',
                        'alias' => 'Hapus HSSE'
                    ],
                ]
            ],
            [
                'group_name' => 'Pengaturan Postingan RAM',
                'permission_attribute' => [
                    [
                        'name' => 'post-list',
                        'alias' => 'Halaman Utama RAM'
                    ],
                    [
                        'name' => 'post-create',
                        'alias' => 'Tambah RAM'
                    ],
                    [
                        'name' => 'post-edit',
                        'alias' => 'Edit RAM'
                    ],
                    [
                        'name' => 'post-delete',
                        'alias' => 'Hapus RAM'
                    ],
                    [
                        'name' => 'post-filter',
                        'alias' => 'Filter RAM'
                    ]
                ]
            ],
            [
                'group_name' => 'Pengaturan Well Program',
                'permission_attribute' => [
                    [
                        'name' => 'program-list',
                        'alias' => 'Halaman Utama Well Program'
                    ],
                    [
                        'name' => 'program-create',
                        'alias' => 'Tambah Well Program'
                    ],
                    [
                        'name' => 'program-edit',
                        'alias' => 'Edit Well Program'
                    ],
                    [
                        'name' => 'program-delete',
                        'alias' => 'Hapus Well Program'
                    ]
                ]
            ],
            [
                'group_name' => 'Pengaturan Peserta',
                'permission_attribute' => [
                    [
                        'name' => 'participant-list',
                        'alias' => 'Halaman utama peserta'
                    ],
                    [
                        'name' => 'participant-detail',
                        'alias' => 'Detail Peserta'
                    ],
                    [
                        'name' => 'participant-download',
                        'alias' => 'Download Peserta'
                    ],
                ]
            ],
            [
                'group_name' => 'Pengaturan Inforial',
                'permission_attribute' => [
                    [
                        'name' => 'inforial-show',
                        'alias' => 'Update inforial'
                    ],
                ]
            ],
            [
                'group_name' => 'Pengaturan Laporan',
                'permission_attribute' => [
                    [
                        'name' => 'report-list',
                        'alias' => 'Halaman utama laporan'
                    ],
                    [
                        'name' => 'report-filter',
                        'alias' => 'Filter by kecamatan'
                    ],
                    [
                        'name' => 'report-download',
                        'alias' => 'Download'
                    ],
                    [
                        'name' => 'report-filterby-user',
                        'alias' => 'Tampilkan Berdasarkan SKPD'
                    ],
                ]
            ],
            [
                'group_name' => 'Pengaturan Pengaduan',
                'permission_attribute' => [
                    [
                        'name' => 'complaint-list',
                        'alias' => 'Halaman utama pengaduan'
                    ],
                    [
                        'name' => 'complaint-reply',
                        'alias' => 'Balas pengaduan'
                    ],
                ]
            ],
            [
                'group_name' => 'Pengaturan User',
                'permission_attribute' => [
                    [
                        'name' => 'user-list',
                        'alias' => 'Halaman Utama List User'
                    ],
                    [
                        'name' => 'user-create',
                        'alias' => 'Tambah User'
                    ],
                    [
                        'name' => 'user-edit',
                        'alias' => 'Edit User'
                    ],
                    [
                        'name' => 'user-delete',
                        'alias' => 'Hapus User'
                    ],
                ]
            ],
            [
                'group_name' => 'Pengaturan Role',
                'permission_attribute' => [
                    [
                        'name' => 'role-list',
                        'alias' => 'Halaman Utama List Role'
                    ],
                    [
                        'name' => 'role-create',
                        'alias' => 'Tambah Role'
                    ],
                    [
                        'name' => 'role-edit',
                        'alias' => 'Edit Role'
                    ],
                    [
                        'name' => 'role-delete',
                        'alias' => 'Hapus Role'
                    ],
                ]
            ],

            [
                'group_name' => 'Pengaturan Halaman Dashboard',
                'permission_attribute' => [
                    [
                        'name' => 'guest-dashboard',
                        'alias' => 'Dashboard User'
                    ],
                    [
                        'name' => 'admin-dashboard',
                        'alias' => 'Dashboard Admin'
                    ],
                    [
                        'name' => 'admin-dashboard-byself',
                        'alias' => 'Dashboard Manager'
                    ],
                ]
            ],

            [
                'group_name' => 'Pengaturan Halaman Infosurat',
                'permission_attribute' => [
                    [
                        'name' => 'infosurat-list',
                        'alias' => 'Halaman Utama List Info Surat'
                    ],
                    [
                        'name' => 'infosurat-create',
                        'alias' => 'Tambah Info Surat'
                    ],
                    [
                        'name' => 'infosurat-edit',
                        'alias' => 'Edit Info Surat'
                    ],
                    [
                        'name' => 'infosurat-delete',
                        'alias' => 'Hapus Info Surat'
                    ],
                ]
            ],
        ];
        foreach ($permissions as $permission) {
            $permission_group = PermissionGroup::create([
                'group_name' => $permission['group_name'],
            ]);

            foreach ($permission['permission_attribute'] as $key => $value) {
                $permissions = Permission::create(['name' => $value['name']]);

                $permission_detail = PermissionDetail::create([
                    'alias' => $value['alias'],
                    'permission_id' => $permissions->id,
                    'group_id' => $permission_group->id
                ]);
            }
        }
    }
}
