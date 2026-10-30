<?php

namespace App\Console\Commands;

use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Import rekap "Contact Us DMC Web" dari Gmail (sebelum contact_inquiry ada,
 * form hanya dikirim lewat email). Aman dijalankan ulang: baris dengan
 * email_message_id yang sama dilewati.
 */
class ImportContactInquiries extends Command
{
    protected $signature = 'contact-inquiry:import {file : Path file JSON rekap Gmail} {--dry-run : Tampilkan saja, tidak menyimpan}';
    protected $description = 'Import rekap Contact Us DMC Web (JSON dari Gmail) ke tabel contact_inquiry';

    public function handle(): int
    {
        $path = $this->argument('file');
        $json = is_file($path) ? json_decode(file_get_contents($path), true) : null;
        if (!is_array($json) || !isset($json['data'])) {
            $this->error('File tidak ditemukan atau bukan JSON rekap yang valid: ' . $path);
            return 1;
        }

        $rows = [];
        $skipped = 0;
        foreach ($json['data'] as $entry) {
            $row = $this->toRow($entry);
            if ($row) {
                $rows[] = $row;
            } else {
                $skipped++;
            }
        }

        $hasTable = Schema::hasTable('contact_inquiry');
        if (!$hasTable && !$this->option('dry-run')) {
            $this->error('Tabel contact_inquiry belum ada; jalankan migration-nya dulu.');
            return 1;
        }
        $existing = $hasTable
            ? ContactInquiry::whereIn('email_message_id', array_column($rows, 'email_message_id'))->pluck('email_message_id')->all()
            : [];
        $new = array_values(array_filter($rows, function ($row) use ($existing) {
            return !in_array($row['email_message_id'], $existing, true);
        }));

        $this->table(['Tanggal', 'Nama', 'Email', 'Pertanyaan'], array_map(function ($row) {
            return [$row['created_at'], trim($row['first_name'] . ' ' . $row['last_name']), $row['email'], mb_strimwidth((string) $row['message'], 0, 60, '...')];
        }, $new));
        $this->info(count($new) . ' baru, ' . count($existing) . ' sudah ada, ' . $skipped . ' dilewati (auto-reply / forward duplikat).');

        if ($this->option('dry-run')) {
            $this->comment('Dry run: tidak ada yang disimpan.');
            return 0;
        }
        foreach ($new as $row) {
            $row['users_id'] = optional(User::where('email', $row['email'])->first())->id;
            $inquiry = new ContactInquiry($row);
            $inquiry->email_message_id = $row['email_message_id'];
            $inquiry->created_at = $row['created_at'];
            $inquiry->updated_at = $row['created_at'];
            $inquiry->save();
        }
        $this->info(count($new) . ' inquiry tersimpan.');
        return 0;
    }

    /**
     * form_masuk = form asli; forward = form yang diteruskan (dilewati kalau sudah ada
     * sebagai form_masuk); email_manual = balasan yang mengutip form asli. Tanggal pakai
     * waktu form dikirim (WIB, sama dengan timezone aplikasi).
     */
    private function toRow(array $entry): ?array
    {
        $type = $entry['tipe'] ?? '';
        $body = $entry['body'] ?? [];
        if ($type === 'form_masuk') {
            $form = $body['form'] ?? null;
            $date = $entry['tanggal_wib'] ?? null;
        } elseif ($type === 'forward' && empty($entry['duplikat_form_masuk'])) {
            $form = $body['form'] ?? null;
            $date = $body['tanggal_form_wib'] ?? $entry['tanggal_wib'] ?? null;
        } elseif ($type === 'email_manual') {
            $form = $body['form_asli_yang_dikutip'] ?? null;
            $date = $form['date_wib'] ?? $entry['tanggal_wib'] ?? null;
        } else {
            return null;
        }
        if (!$form || empty($form['email']) || !$date) {
            return null;
        }

        $name = preg_split('/\s+/', trim((string) ($form['name'] ?? '')), 2);
        return [
            'source'           => ContactInquiry::SOURCE_WEB,
            'first_name'       => $name[0] ?? null,
            'last_name'        => $name[1] ?? null,
            'email'            => trim($form['email']),
            'phone'            => $form['phone'] ?? null,
            'message'          => $form['question'] ?? null,
            'email_message_id' => $entry['message_id'],
            'created_at'       => $date . ':00',
        ];
    }
}
