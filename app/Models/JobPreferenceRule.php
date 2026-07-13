<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class JobPreferenceRule extends Model
{
    protected $guarded = ['id', 'user_id'];

    protected function casts(): array
    {
        return ['comparison_value_json' => 'encrypted:array', 'reason' => 'encrypted', 'confidence' => 'decimal:2', 'active' => 'boolean', 'expires_at' => 'datetime', 'confirmed_at' => 'datetime'];
    }

    public function scopeApplicable(Builder $query): Builder
    {
        return $query->where('active', true)->whereNotNull('confirmed_at')->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function evaluations()
    {
        return $this->hasMany(JobRuleEvaluation::class, 'preference_rule_id');
    }

    public function sourceJob()
    {
        return $this->belongsTo(JobOpportunity::class, 'source_job_opportunity_id');
    }

    public function sourceReview()
    {
        return $this->belongsTo(OpportunityReviewHistory::class, 'source_review_history_id');
    }
}
