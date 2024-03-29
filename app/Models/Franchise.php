<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Franchise extends Model
{
    use HasFactory;
    protected $table = 'franchises';
    protected $guarded = [];
    
    public function category() {
        return $this->belongsTo(FranchiseCategory::class, 'franchise_category_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'boughtBy');
    }

    public function franchisor() {
        return $this->belongsTo(User::class, 'franchisePIC');
    }
}
