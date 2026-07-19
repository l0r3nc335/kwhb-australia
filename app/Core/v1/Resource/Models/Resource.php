<?php

namespace App\Core\v1\Resource\Models;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $with = ['createdBy', 'employees', 'roles'];
    protected $appends = ['type_icon', 'resource_tag_name'];

    const TYPE_FILE = 1;
    const TYPE_LINK = 2;

    const PRIVACY_PUBLIC = 1;
    const PRIVACY_RESTRICTED = 2;
    const PRIVACY_ONLY_ME = 3;
    const PRIVACY_ARCHIVED = 4;

    public function owner()
    {
        return $this->belongsTo(User::class, 'owned_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected $fillable = [
        'owned_by',
        'created_by',
        'due_date',
        'type',
        'name',
        'description',
        'path',
        'extension',
        'resource_category_id',
        'privacy',
        'acknowledgement_required',
        'mime_type',
        'assigned_to',
        'is_acknowledged',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'acknowledgement_required' => 'boolean',
        'is_acknowledged' => 'boolean',
    ];    

    public function getTypeIconAttribute()
    {
        $iconPath = '/assets/images/icons/';
        $mimeTypeArr = explode('/', $this->mime_type);

        if ($this->type == self::TYPE_LINK) {
            $iconPath .= 'link.png';
            return $iconPath;
        }

        switch ($mimeTypeArr[0]) {
            case 'audio':
                $iconPath .= 'audio.png';
                break;
            case 'video':
                $iconPath .= 'video.png';
                break;
            case 'image':
                $iconPath .= 'image.png';
                break;
            case 'application':
                if (str_contains($mimeTypeArr[1], 'word')) {
                    $iconPath .= 'word.png';
                } elseif (str_contains($mimeTypeArr[1], 'pdf')) {
                    $iconPath .= 'pdf.png';
                } elseif (str_contains($mimeTypeArr[1], 'spreadsheet') || str_contains($mimeTypeArr[1], 'excel')) {
                    $iconPath .= 'excel.png';
                } elseif (str_contains($mimeTypeArr[1], 'powerpoint')) {
                    $iconPath .= 'powerpoint.png';
                } elseif (str_contains($mimeTypeArr[1], 'zip') || str_contains($mimeTypeArr[1], 'rar')) {
                    $iconPath .= 'compressed.png';
                }
                break;
            default:
                $iconPath .= 'file.png';
                break;
        }

        return $iconPath;
    }

    public function employees()
    {
        return $this->belongsToMany(\App\Models\Employee::class, 'employee_resource');
    }
    
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class);
    }
    
    public function tags()
    {
        // Adjust ResourceTag namespace if needed
        return $this->belongsToMany(
            \App\Core\v1\Resource\Models\ResourceTag::class,
            'resource_tag',           // Pivot table
            'resource_id',            // This model's key on pivot
            'resource_tag_id'         // Related model's key on pivot
        );
    }
    
    public function category()
    {
        return $this->belongsTo(
            \App\Core\v1\Resource\Models\ResourceCategory::class,
            'resource_category_id'
        );
    }    


    public function getResourceTagNameAttribute()
    {
        // Get first tag if exists
        $firstTag = $this->tags->first();
        return $firstTag ? $firstTag : null;
    }  
    

    public function creatorRoles()
    {
        // Return relationship (for chaining)
        return $this->createdBy()->with('roles');
    }

    public function getCreatorRolesAttribute()
    {
        return $this->createdBy ? $this->createdBy->roles : collect();
    }

    public function getCreatorRoleAttribute()
    {
        return $this->createdBy && $this->createdBy->roles->isNotEmpty()
            ? $this->createdBy->roles->first()
            : null;
    }

}
