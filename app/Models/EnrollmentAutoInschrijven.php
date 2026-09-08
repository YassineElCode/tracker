<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentAutoInschrijven extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'enrollment_auto_inschrijven';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'examencentrum',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
