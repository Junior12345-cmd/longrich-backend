<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','title','description','logo','banner','adresse','mail',
        'option','status','solde','title_principal_shop','text_description_shop','lien_shop','theme','seo_meta','views_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function isComplete(): bool
    {
        $importantFields = [
            'title_principal_shop',
            'text_description_shop',
            'title',
            'description',
            'logo',
            'mail',
        ];

        foreach ($importantFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        return true;
    }

    // protected static function booted()
    // {
    //     static::saving(function ($shop) {
    //         if ($shop->status !== 'inactive') {
    //             $shop->status = $shop->isComplete() ? 'complete' : 'incomplete';
    //         }
    //     });
    // }




}
