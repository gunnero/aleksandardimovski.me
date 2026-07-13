<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateProfile extends Model
{
    protected $guarded = ['id', 'user_id'];

    protected function casts(): array
    {
        return [
            'professional_email' => 'encrypted', 'phone' => 'encrypted', 'salary_minimum' => 'encrypted', 'salary_target' => 'encrypted', 'notice_period' => 'encrypted', 'availability' => 'encrypted', 'work_authorization_notes' => 'encrypted',
            'education_json' => 'encrypted:array', 'languages_json' => 'encrypted:array', 'verified_skills_json' => 'encrypted:array', 'employment_history_json' => 'encrypted:array', 'standard_answers_json' => 'encrypted:array', 'field_states_json' => 'encrypted:array',
        ];
    }
}
