<?php

namespace App\Observers;

use App\Models\Jadwal;
use App\Models\Kehadiran;

class KehadiranObserver
{
    public function creating(Kehadiran $kehadiran): void
    {
        if ($kehadiran->jadwal_id) {
            if (! $kehadiran->tutor_id) {
                $kehadiran->tutor_id = Jadwal::query()->whereKey($kehadiran->jadwal_id)->value('tutor_id');
            }
        }

        if ($kehadiran->created_by === null && auth()->check()) {
            $kehadiran->created_by = auth()->id();
        }
    }

    public function updating(Kehadiran $kehadiran): void
    {
        if ($kehadiran->isDirty('jadwal_id') && ! $kehadiran->isDirty('tutor_id')) {
            $kehadiran->tutor_id = Jadwal::query()->whereKey($kehadiran->jadwal_id)->value('tutor_id');
        }
    }
}
