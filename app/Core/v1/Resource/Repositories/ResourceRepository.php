<?php

namespace App\Core\v1\Resource\Repositories;

use App\Core\v1\Resource\Models\Resource;
use App\Core\v1\Resource\Models\ResourceCategory;
use App\Core\v1\Resource\Models\ResourceTag;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ResourceRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Resource;
    }

    public function categories()
    {
        return ResourceCategory::all();
    }
    public function tags()
    {
        return ResourceTag::all();
    }

    public function createCategory(string $name, string $color = null, string $background = null): Model
    {
        $background = str_replace('public', 'storage', $background);
        $category = new ResourceCategory;
        $category->name = $name;
        $category->color = $color;
        $category->background = $background;
        $category->save();

        return $category;
    }
    
    public function createTag(string $name, string $color): Model
    {
        $tag = new ResourceTag;
        $tag->name = $name;
        $tag->color = $color;
        $tag->save();

        return $tag;
    }
    
    public function createResourceTag(int $resourceId, int $resourceTagId) : Model
    {
   
        
    }

    public function store(
        int $categoryId,
        string $name,
        string|null $filePath,
        int $type,
        string $mimeType = null,
        int $ownerId = null,
        string $extension = null,
        string $description = null,
        string $link = null,
        int $privacy = null,
        bool $acknowledgementRequired = false,
        string $due_date = null
    ): Model
    {
        // Map privacy to your allowed status values only
        if ($privacy === 1) {
            $str_privacy = "everyone";
        } else if ($privacy === 2) {
            $str_privacy = "user";
        } else if ($privacy === 3) {
            $str_privacy = "only me";
        } else if ($privacy === 5) {
            $str_privacy = "role";
        } else {
            // fallback to 'everyone' or handle error as you wish
            $str_privacy = "everyone";
        }

        $path = ($type === Resource::TYPE_FILE) ? $filePath : $link;
        $resource = $this->model();
        $resource->resource_category_id = $categoryId;
        $resource->name = $name;
        $resource->description = $description;
        $resource->path = $path;
        $resource->extension = $extension;
        $resource->mime_type = $mimeType;
        $resource->type = $type;
        $resource->owned_by = $ownerId;
        $resource->created_by = auth()->user()->id;
        $resource->status = $str_privacy;
        $resource->acknowledgement_required = $acknowledgementRequired;
        if ($due_date) {
            $dt = \Carbon\Carbon::parse($due_date, 'Australia/Sydney')->setTimezone('UTC');
            $resource->due_date = $dt->toDateTimeString(); // If your column is DATETIME
        } else {
            $resource->due_date = null;
        }
        $resource->save();

        return $resource;
    }


    public function uploadResource(UploadedFile $file)
    {
        $path = null;
        $extension = null;
        $mimeType = null;

        try {
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $path = $file->store('public/resources');
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }


        return ['path' => $path, 'extension' => $extension, 'mimeType' => $mimeType];
    }


    /**
     * Update the resource.
     *
     * @param Resource $resource
     * @param int $categoryId
     * @param string $name
     * @param string|null $filePath
     * @param int $type
     * @param string|null $mimeType
     * @param int|null $ownerId
     * @param string|null $extension
     * @param string|null $description
     * @param string|null $link
     * @return Resource
     */
    public function update(
        Resource $resource,
        int $categoryId,
        string $name,
        string|null $filePath,
        int $type,
        string|null $mimeType,
        int|null $ownerId,
        string|null $extension,
        string|null $description,
        string|null $link,
        int $privacy = 1,
        bool $acknowledgementRequired = false,
        string $due_date = null
    ): Resource
    {
        // Privacy translation
        $status = "everyone";
        if ($privacy === 1) $status = "everyone";
        else if ($privacy === 2) $status = "user";
        else if ($privacy === 3) $status = "only me";
        else if ($privacy === 4) $status = "archived";
        else if ($privacy === 5) $status = "role";

        $resource->resource_category_id = $categoryId;
        $resource->name = $name;
        $resource->description = $description;
        $resource->path = ($type === Resource::TYPE_FILE) ? $filePath : $link;
        $resource->extension = $extension;
        $resource->mime_type = $mimeType;
        $resource->type = $type;
        $resource->owned_by = $ownerId;
        $resource->status = $status;
        $resource->acknowledgement_required = $acknowledgementRequired;
        $resource->due_date = $due_date;

        $resource->save();

        return $resource;
    }

}
