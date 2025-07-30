<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Opsional: Untuk mengatur lebar kolom otomatis

class DtiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize // Tambahkan ShouldAutoSize
{
    protected $allUsersData;

    public function __construct(array $allUsersData)
    {
        $this->allUsersData = $allUsersData;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Pastikan data yang dikirim ke sini sudah dalam bentuk array,
        // lalu diubah menjadi Collection.
        return new Collection($this->allUsersData);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Ini adalah header kolom yang akan muncul di file Excel Anda.
        // Sesuaikan urutannya dengan data yang Anda kembalikan di metode map().
        return [
            'No.',
            'Username',
            'Nama Lengkap', // Contoh judul kustom
            'Email',
            'Telepon',
            'Last Name',
            'Registered As', // Contoh judul kustom
            'Posisi Jabatan', // Contoh judul kustom
            'Level Jabatan', // Contoh judul kustom
            'Fungsi Jabatan', // Contoh judul kustom
            'Perusahaan',
            'Negara',
            'Link Foto', // Contoh: tampilkan URL saja
            'Link LinkedIn' // Contoh: tampilkan URL saja
        ];
    }

    /**
     * @param mixed $user
     * @return array
     */
    public function map($user): array
    {
        // Ini adalah cara data dari setiap objek `$user` akan dipetakan ke baris Excel.
        // Pastikan urutan item di array ini sesuai dengan `headings()` di atas.
        static $rowNum = 0; // Untuk nomor urut di Excel
        $rowNum++;

        return [
            $rowNum,
            $user['Username'] ?? '',
            $user['Nama'] ?? '',
            $user['Email'] ?? '',
            $user['Telepon'] ?? '',
            $user['LastName'] ?? '',
            $user['RegAs'] ?? '',
            $user['JobTitle'] ?? '',
            $user['JobLevel'] ?? '',
            $user['JobFunction'] ?? '',
            $user['Company'] ?? '',
            $user['Country'] ?? '',
            $user['Photo'] ?? '',
            $user['Linkedin'] ?? ''
        ];
    }
}
