<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    protected $primaryKey = "postid";

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "userid", "userid");
    }

    protected $fillable = [
        'userid', 'post', 'postpic', 'likes', 'comments'
        //Tambahkan atribut lainnya yang ingin Anda izinkan untuk dimasukkan secara massal
    ];
}
