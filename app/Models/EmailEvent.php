<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['sender' => 'encrypted', 'subject' => 'encrypted', 'notes' => 'encrypted', 'received_at' => 'datetime', 'processed_at' => 'datetime', 'action_required' => 'boolean'];
    }
}
