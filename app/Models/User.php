<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\HoeveelstePoging;
use App\Enums\TypeVoorlopigRijbewijs;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'voornaam',
        'achternaam',
        'email',
        'password',
        'whatsapp',
        'startDatum',
        'endDatum',
        'startUur',
        'endUur',
        'rrn',
        'gbdatum',
        'tel',
        'adres',
        'postcode',
        'zeersteVRijbewijsDatum',
        'zhuidigVRijbewijsDatum',
        'zhuidigVRijbewijsGeldigTot',
        'sbat_email',
        'sbat_password',
        'datum_slagen_theorieB',
        'type_voorlopig_rijbewijs',
        'afgiftedatum_voorlopig_rijbewijsB',
        'hoeveelste_poging',
        'send_notifications',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
        'sbat_password',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type_voorlopig_rijbewijs' => TypeVoorlopigRijbewijs::class,
            'hoeveelste_poging' => HoeveelstePoging::class,
        ];
    }

    protected function sbatPassword(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if ($value === null) {
                    return null;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    return null;
                }
            },
            set: fn (?string $value): ?string => $value !== null ? Crypt::encryptString($value) : null,
        );
    }

    public function enrollmentAutoInschrijven(): HasMany
    {
        return $this->hasMany(EnrollmentAutoInschrijven::class);
    }

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class);
    }
}
