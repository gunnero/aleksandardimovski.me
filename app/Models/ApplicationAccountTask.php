<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationAccountTask extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['account_email' => 'encrypted', 'notes' => 'encrypted', 'account_created_at' => 'datetime', 'verification_requested_at' => 'datetime', 'verification_completed_at' => 'datetime'];
    }
}
