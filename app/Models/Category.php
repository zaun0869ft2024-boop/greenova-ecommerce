<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'cat_id';

    public $timestamps = false;

    protected $fillable = [
        'cat_title',
        'cat_description'
    ];

    public function products()
    {
        return $this->hasMany(
            Product::class,
            'product_cat',
            'cat_id'
        );
    }
}