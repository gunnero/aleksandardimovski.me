<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRuleEvaluation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['matched' => 'boolean', 'explanation' => 'encrypted', 'evaluated_at' => 'datetime'];
    }

    public function rule()
    {
        return $this->belongsTo(JobPreferenceRule::class, 'preference_rule_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(JobOpportunity::class, 'job_opportunity_id');
    }
}
