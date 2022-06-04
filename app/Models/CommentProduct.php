<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CommentProduct extends Model
{
    use HasFactory;

    protected $table = 'comment_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'comment',
    ];

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}
