<?php

namespace App\Core\v1\Acknowledgement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Core\v1\Resource\Models\Resource; // Adjust path if needed
use App\Models\User; // Adjust path if needed

class Acknowledgement extends Model
{
    use HasFactory;

    protected $table = 'acknowledgement';

    // Eager-load the related resource by default
    protected $with = ['resource', 'user'];

    // Mass-assignable fields
    protected $fillable = [
        'resource_id',
        'is_acknowledged',
        'user_id',
    ];

    /**
     * Relationship: Acknowledgement belongs to a Resource.
     */
    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }

    /**
     * Relationship: Acknowledgement belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
