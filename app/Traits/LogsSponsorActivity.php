<?php

namespace App\Traits;

use App\Models\Sponsors\SponsorLog;

/**
 * Pasang di model sponsor: setiap create / update / delete lewat Eloquent tercatat
 * di sponsor_logs. Update/delete massal (Model::where()->delete()) tidak lewat
 * event model, jadi di controller dilakukan per record.
 *
 * Model bisa mengatur:
 * - $sponsorLogSubject  label bagian (default: nama class)
 * - sponsorLogSponsorId() kalau sponsor_id tidak langsung ada di model
 */
trait LogsSponsorActivity
{
    /** Kolom teknis yang tidak perlu dicatat sebagai perubahan. */
    protected static $sponsorLogIgnored = ['created_at', 'updated_at', 'deleted_at', 'remember_token'];

    public static function bootLogsSponsorActivity(): void
    {
        static::created(function ($model) {
            $model->writeSponsorLog('created', $model->sponsorLogValues($model->getAttributes()));
        });

        static::updated(function ($model) {
            $changes = [];
            foreach ($model->getChanges() as $field => $new) {
                if (in_array($field, static::$sponsorLogIgnored, true)) {
                    continue;
                }
                $old = $model->getOriginal($field);
                if ((string) $old === (string) $new) {
                    continue;
                }
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
            if ($changes) {
                $model->writeSponsorLog('updated', $changes);
            }
        });

        static::deleted(function ($model) {
            $model->writeSponsorLog('deleted', $model->sponsorLogValues($model->getOriginal(), true));
        });
    }

    public function sponsorLogSponsorId(): ?int
    {
        return $this->sponsor_id ? (int) $this->sponsor_id : null;
    }

    public function sponsorLogSubject(): string
    {
        return property_exists($this, 'sponsorLogSubject') ? $this->sponsorLogSubject : class_basename($this);
    }

    /** Snapshot nilai untuk created (old kosong) atau deleted (new kosong). */
    protected function sponsorLogValues(array $values, bool $asOld = false): array
    {
        $out = [];
        foreach ($values as $field => $value) {
            if (in_array($field, static::$sponsorLogIgnored, true) || $field === $this->getKeyName() || $value === null || $value === '') {
                continue;
            }
            $out[$field] = $asOld ? ['old' => $value, 'new' => null] : ['old' => null, 'new' => $value];
        }
        return $out;
    }

    protected function writeSponsorLog(string $action, array $changes): void
    {
        SponsorLog::record($this->sponsorLogSponsorId(), $this->sponsorLogSubject(), $this->getKey(), $action, $changes);
    }
}
