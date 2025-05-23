<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activities extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'tasks_id',
        'nama_aktivitas',
        'uraian',
        'tanggal',
        'status',
        'users_id'
    ];

    public $timestamps = false;

    protected $primaryKey = 'id';
    public $incrementing = true;

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'tasks_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(users::class, 'users_id', 'id');
    }

}
