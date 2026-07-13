<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class JobOpportunity extends Model
{
    protected $guarded = ['id', 'user_id', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return ['posting_date' => 'date', 'discovered_at' => 'datetime', 'application_deadline' => 'date', 'source_verified_at' => 'datetime', 'reviewed_at' => 'datetime', 'technology_stack_json' => 'array', 'required_experience_json' => 'array', 'preferred_experience_json' => 'array', 'strengths_json' => 'array', 'gaps_json' => 'array', 'risks_json' => 'array'];
    }

    public function application()
    {
        return $this->hasOne(JobApplication::class);
    }

    public function reviewHistory()
    {
        return $this->hasMany(OpportunityReviewHistory::class)->latest('reviewed_at');
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->whereIn('review_status', ['discovered', 'needs_review']);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('review_status', 'approved_for_preparation');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('review_status', 'rejected');
    }

    public function scopeSaved(Builder $query): Builder
    {
        return $query->where('review_status', 'saved_for_later');
    }

    public function scopeNeedsResearch(Builder $query): Builder
    {
        return $query->where('review_status', 'needs_research');
    }

    public function scopeDuplicates(Builder $query): Builder
    {
        return $query->where('review_status', 'duplicate');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('review_status', 'expired');
    }
}
