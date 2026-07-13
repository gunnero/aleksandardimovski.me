<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOpportunity extends Model
{
    protected $guarded = ['id', 'user_id', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return ['posting_date' => 'date', 'discovered_at' => 'datetime', 'application_deadline' => 'date', 'source_verified_at' => 'datetime', 'technology_stack_json' => 'array', 'required_experience_json' => 'array', 'preferred_experience_json' => 'array', 'strengths_json' => 'array', 'gaps_json' => 'array', 'risks_json' => 'array'];
    }

    public function application()
    {
        return $this->hasOne(JobApplication::class);
    }
}
