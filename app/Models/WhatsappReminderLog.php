<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappReminderLog extends Model
{
    protected $table = 'whatsapp_reminder_logs';

    protected $fillable = [
        'dedupe_key',
        'type',
    ];
}
