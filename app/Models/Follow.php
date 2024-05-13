<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    use HasFactory;

    protected $primaryKey = 'followid';
    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following', 'userid');
    }
    
    public function followed(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followed', 'userid');
    }

    protected $fillable = ['following', 'followed'];
}
