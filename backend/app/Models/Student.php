<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'ic_hash',
        'encrypted_ic',
        'encrypted_name',
        'school_name',
    ];

    protected $hidden = [
        'encrypted_ic',
        'encrypted_name',
    ];

    // Relationships
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'class_students', 'student_id', 'class_id')
            ->withTimestamps();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    // Accessors for decrypted values
    public function getNameAttribute(): string
    {
        return Crypt::decryptString($this->encrypted_name);
    }

    public function getIcAttribute(): string
    {
        return Crypt::decryptString($this->encrypted_ic);
    }

    // Static helper for IC hashing
    public static function hashIc(string $ic): string
    {
        return hash('sha256', $ic);
    }

    // Find or create student by IC
    public static function findOrCreateByIc(string $ic, string $name, ?string $schoolName = null): self
    {
        $icHash = self::hashIc($ic);
        
        return self::firstOrCreate(
            ['ic_hash' => $icHash],
            [
                'encrypted_ic' => Crypt::encryptString($ic),
                'encrypted_name' => Crypt::encryptString($name),
                'school_name' => $schoolName,
            ]
        );
    }
}
