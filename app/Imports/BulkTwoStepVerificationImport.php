<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Bulk-set `two_step_verified` (Verifikasi 2-Langkah oleh Staff, mis. via
 * LinkedIn/Telfon) dari daftar email di Excel — sama persis efeknya dengan
 * toggle satuan di UsersController::toggleTwoStep(), cuma diterapkan ke
 * banyak member sekaligus. Heading row otomatis dinormalisasi Maatwebsite
 * jadi snake_case, jadi kolom "Email"/"Status" di spreadsheet asal langsung
 * cocok ke field di bawah.
 *
 * Match key: email (case-insensitive, exact) terhadap users.email — TIDAK
 * dicek ke alternative_email, supaya gak ambigu kalau dua member beda
 * kebetulan share alternative_email yang sama.
 *
 * Status diterima fleksibel: "verified"/"yes"/"ya"/"true"/"1" → true,
 * "not verified"/"no"/"tidak"/"false"/"0"/kosong → false. Row dengan status
 * yang sudah sama persis dengan kondisi member saat ini dihitung "unchanged"
 * (tidak nulis ulang ke DB, tidak mengubah two_step_verified_at/_by).
 */
class BulkTwoStepVerificationImport implements ToCollection, WithHeadingRow
{
    private string $performedBy;
    private int $verified = 0;
    private int $unverified = 0;
    private int $unchanged = 0;
    private int $notFound = 0;
    private array $errors = [];

    private const TRUE_VALUES = ['verified', 'yes', 'ya', 'true', '1'];

    public function __construct(string $performedBy)
    {
        $this->performedBy = $performedBy;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '') {
                continue;
            }

            $status = strtolower(trim((string) ($row['status'] ?? '')));
            $wantVerified = in_array($status, self::TRUE_VALUES, true);

            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user) {
                $this->notFound++;
                $this->errors[] = "Row {$rowNum}: email \"{$email}\" tidak ditemukan.";
                continue;
            }

            if ((bool) $user->two_step_verified === $wantVerified) {
                $this->unchanged++;
                continue;
            }

            $user->two_step_verified = $wantVerified;
            $user->two_step_verified_at = $wantVerified ? now() : null;
            $user->two_step_verified_by = $wantVerified ? $this->performedBy : null;
            $user->save();

            if ($wantVerified) {
                $this->verified++;
            } else {
                $this->unverified++;
            }
        }
    }

    public function getVerified(): int
    {
        return $this->verified;
    }

    public function getUnverified(): int
    {
        return $this->unverified;
    }

    public function getUnchanged(): int
    {
        return $this->unchanged;
    }

    public function getNotFound(): int
    {
        return $this->notFound;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
