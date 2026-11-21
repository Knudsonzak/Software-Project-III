<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $primaryKey = 'leave_type_id';

    protected $fillable = [
        'name',
        'default_days',
    ];

    public function leaveForms(): HasMany
    {
        return $this->hasMany(LeaveForm::class, 'leave_type_id', 'leave_type_id');
    }
}