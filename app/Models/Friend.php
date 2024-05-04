<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friend extends Model
{
    use HasFactory;

    protected $primaryKey = "friendshipid";
    public function userfriend(): BelongsTo
    {
        return $this->belongsTo(User::class, "userid1", "userid");
    }
    
    public function frienduser(): BelongsTo
    {
        return $this->belongsTo(User::class, "userid2", "userid");
    }
}
