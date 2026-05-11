<?php

namespace App\Observers;

use App\Models\KehadiranSiswa;

class KehadiranSiswaObserver
{
    public function creating(KehadiranSiswa $kehadiran): void
    {
        if ($kehadiran->created_by === null && auth()->check()) {
            $kehadiran->created_by = auth()->id();
        }
    }
}
