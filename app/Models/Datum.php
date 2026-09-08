<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Datum extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'datum';

    /** @var list<string> */
    protected $fillable = [
        'city_id',
        'olddatums',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'olddatums' => 'array',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
