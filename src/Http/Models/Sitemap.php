<?php

namespace Orbitali\Http\Models;

use Orbitali\Http\Traits\Cacheable;
use Orbitali\Http\Traits\ExtendExtra;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sitemap extends Model
{
    use SoftDeletes, Cacheable, ExtendExtra;

    protected $table = 'sitemaps';
    protected $guarded = [];
    protected $withoutExtra = ['id', 'website_id', 'detail', 'search', 'category', 'user_id', 'status', 'created_at', 'updated_at', 'deleted_at'];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function urls()
    {
        return $this->hasManyThrough(Url::class, SitemapDetail::class, null, 'model_id')->where('model_type', SitemapDetail::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function extras()
    {
        return $this->hasMany(SitemapExtra::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function details()
    {
        return $this->hasMany(SitemapDetail::class);
    }
}
