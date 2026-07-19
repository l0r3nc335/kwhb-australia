<?php

namespace App\Core\v1\Template\Repositories;

use App\Core\v1\Template\Models\SharedTemplate;
use App\Core\v1\Template\Models\Template;
use App\Core\v1\Template\Models\TemplateField;
use App\Core\v1\Template\Models\TemplateVersionHistory;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TemplateRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Template();
    }

    public function getTemplatesByType($type)
    {
        $templates = Template::where('type', $type)->get();

        return $templates;
    }

    public function getTemplatesByTypeAndStatus($type, $status)
    {
        $templates = Template::where('type', $type)
            ->where('status', $status)
            ->get();

        return $templates;
    }

    public function getTemplatesByTypeAndOwnerEmployeeId($type, $ownerEmployeeId)
    {
        $templates = Template::where('type', $type)
            ->where('owner_employee_id', $ownerEmployeeId)
            ->get();

        return $templates;
    }

    public function getTemplateById($templateId)
    {
        return Template::where('id', $templateId)
            ->firstOrFail();
    }

    public function getTemplateFieldByTemplateId($templateId)
    {
        return TemplateField::where('template_id', $templateId)
            ->get();
    }

    public function getTemplateVersionHistory($templateId)
    {
        $templateVersionHistory = TemplateVersionHistory::where('template_id', $templateId)
                                                        ->orderBy('created_at', 'desc')
                                                        ->get(); 

        return $templateVersionHistory;
    }

    public function getTemplateFields()
    {
        $templateFileds = TemplateField::whereNull('deleted_at')
            ->get();

        return $templateFileds;
    }

    public function getTemplateFieldsById($templateId)
    {
        $templateFileds = TemplateField::where('template_id', $templateId)
            ->whereNull('deleted_at')
            ->get();

        return $templateFileds;
    }

    public function store(
        int $ownerEmployeeId,
        string $filename,
        string $content,
        string $status,
        string $type,
        string $screenshot = null,
    ): Template {   
        return Template::create([
            'owner_employee_id' => $ownerEmployeeId,
            'screenshot' => $screenshot,
            'filename' => $filename,
            'content' => $content,
            'status' => $status,
            'type' => $type
        ]);
    }

    public function storeTemplateField(
        int $templateId,
        string $fieldName,
        string $index,
        string $type
    ): TemplateField {
        return TemplateField::create([
            'template_id' => $templateId,
            'name' => $fieldName,
            'key_name' => $index,
            'type' => $type,
        ]);
    }

    public function storeTemplateVersionHistory(
        int $templateId,
        string $content,
        string $action,
        string $activity,
        int $user_id,
    ): TemplateVersionHistory {
        return TemplateVersionHistory::create([
            'template_id' => $templateId,
            'content' => $content,
            'action' => $action,
            'activity' => $activity,
            'user_id' => $user_id,
            'created_at' => now(),
        ]);
    }

    public function updateTemplate(
        $templateId,
        string $screenshot = null,
        string $filename = null,
        string $content = null,
        string $status = null
    ): Template {
        $template = Template::where(
            'id',
            $templateId
        )->first();

        $data = [];

        if ($filename !== null) {
            $data['filename'] = $filename;
        }

        if ($content !== null) {
            $data['content'] = $content;
        }

        if ($status !== null) {
            $data['status'] = $status;
        }

        if ($screenshot !== null) {
            $data['screenshot'] = $screenshot;
        }

        $template->update($data);

        return $template;
    }

    public function updateTemplateStatus(
        $templateId,
        string $status
    ): Template {
        $template = Template::where('id', $templateId)->first();
        
        $template->status = $status;

        $template->save();
    
        return $template;
    }

    public function updateTemplateField(
        $fieldId,
        string $fieldName = null,
        string $index = null
    ): TemplateField {
        $field = TemplateField::findOrFail($fieldId);
    
        $data = [];
        if ($fieldName !== null) {
            $data['name'] = $fieldName;
        }
    
        if ($index !== null) {
            $data['key_name'] = $index; 
        }
    
        $field->update($data);
    
        return $field;
    }
    

    public function deleteTemplateById($templateId)
    {
        return Template::where('id', $templateId)
            ->update([
                'status' => 'trash',
                'deleted_at' => now()
            ]);
    }

    public function getsharedTemplate()
    {
        return SharedTemplate::whereNull('deleted_at')
            ->get();
    }

    public function getSharedTemplateById($id)
    {
        return SharedTemplate::where('id', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    public function storeSharedTemplate(
        int $templateId,
        int $ownerEmployeeId,
        int $sharedByEmployeeId,
    ): SharedTemplate {
        return SharedTemplate::create([
            'template_id' => $templateId,
            'owner_employee_id' => $ownerEmployeeId,
            'shared_by_employee_id' => $sharedByEmployeeId,
        ]);
    }

    public function deleteSharedTemplateById($tid)
    {
        return SharedTemplate::where('id', $tid)
            ->update([
                'deleted_at' => now()
            ]);
    }
}
