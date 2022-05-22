<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image',
        'description',
        'price',
        'is_stock',
        'product_status_id', // 0: draft, 1: public
        'category_id',
    ];

    public function category() {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function getAll($request) {

        $result = Product::with('category');

        if (isset($request->keyword)) {
            $result->where('name', 'like', '%'.$request->keyword.'%')
                    ->orWhere('description', 'like', '%'.$request->keyword.'%')
                    ->orWhere('price', 'like', '%'.$request->keyword.'%');
        }

        if (isset($request->category_id)) {
            $category = $request->category_id;
            $result->whereHas('category', function (Builder $query) use($category) {
                $query->whereIn('id', $category);
               });
        }

        // sort = 1 or null (created_at) desc
        // sort = 2 (created_at) asc
        // sort = 3 (price) desc
        // sort = 4 (price) asc
        if(isset($request->sort) && $request->sort == 2)  {

            $result->orderBy('created_at', 'asc');

        } else if(isset($request->sort) && $request->sort == 3)  {

            $result->orderBy('price', 'desc');

        } else if(isset($request->sort) && $request->sort == 4)  {

            $result->orderBy('price', 'asc');

        } else {

            $result->orderBy('created_at', 'desc');
        }
        
        return $result->get();
    }
}
