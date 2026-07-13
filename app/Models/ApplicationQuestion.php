<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationQuestion extends Model
{
    protected $guarded = ['id', 'confirmed_by', 'confirmed_at'];

    protected function casts(): array
    {
        return ['answer' => 'encrypted', 'requires_user_confirmation' => 'boolean', 'legal_or_sensitive' => 'boolean', 'confirmed_at' => 'datetime'];
    }
}
