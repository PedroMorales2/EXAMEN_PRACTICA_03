<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'vacations';
    protected $fillable = ['user_id', 'request_date', 'start_date', 'end_date', 'days', 'status', 'user_available_days_at_moment', 'notes'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
