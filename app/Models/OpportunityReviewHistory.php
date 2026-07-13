<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpportunityReviewHistory extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['review_note' => 'encrypted', 'reviewed_at' => 'datetime'];
    }

    public function opportunity()
    {
        return $this->belongsTo(JobOpportunity::class, 'job_opportunity_id');
    }
}
