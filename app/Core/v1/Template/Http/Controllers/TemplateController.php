<?php

namespace App\Core\v1\Template\Http\Controllers;

use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\Template\Http\Requests\StoreRequest;
use App\Core\v1\Template\Http\Requests\StoreSharedRequest;
use App\Core\v1\Template\Http\Requests\UpdateRequest;
use App\Core\v1\Template\Models\Template;
use App\Core\v1\Template\Models\TemplateField;
use App\Core\v1\Template\Repositories\TemplateRepository;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use LDAP\Result;

class TemplateController extends AppController
{
    public function __construct(protected ModelRepository $repository = new TemplateRepository())
    {
        parent::__construct();
    }

    public function duplicate(Request $request)
    {
        $post = Template::find($request->get('id'));
        if ($post) {
            $newPost = $post->replicate(); // duplicate the model (excluding ID)

            $newPost->filename = $post->filename . '_copy';
            $newPost->status = 'draft';

            $newPost->save(); // save to create a new row with a new ID

            return response()->json(
                ['messsage' => 'Template successfully duplicated.'],
            );
        }
    }

    public function index($type)
    {
        $templates = $this->repository->getTemplatesByType($type);

        $data = [];

        foreach ($templates as $template) {

            $templateFields = $this->repository->getTemplateFieldsById($template->id);
            $data[] = [
                'id' => $template->id,
                'screenshot' => $template->screenshot,
                'templateName' => $template->filename,
                'status' => ucfirst($template->status),
                'content' => $template->content,
                'templateFields' => $templateFields
            ];
        }

        return response()->json($data);
    }
    public function getByTypeAndStatus($type, $status)
    {
        $templates = $this->repository->getTemplatesByTypeAndStatus($type, $status);

        $data = [];

        foreach ($templates as $template) {

            $templateFields = $this->repository->getTemplateFieldsById($template->id);
            $data[] = [
                'id' => $template->id,
                'screenshot' => $template->screenshot,
                'templateName' => $template->filename,
                'status' => ucfirst($template->status),
                'content' => $template->content,
                'templateFields' => $templateFields
            ];
        }

        return response()->json($data);
    }

    public function getByTypeAndOwnerEmployeeId($type, $ownerEmployeeId)
    {
        $templates = $this->repository->getTemplatesByTypeAndOwnerEmployeeId($type, $ownerEmployeeId);

        $data = [];

        foreach ($templates as $template) {

            $templateFields = $this->repository->getTemplateFieldsById($template->id);
            $data[] = [
                'id' => $template->id,
                'screenshot' => $template->screenshot,
                'templateName' => $template->filename,
                'status' => ucfirst($template->status),
                'content' => $template->content,
                'templateFields' => $templateFields
            ];
        }

        return response()->json($data);
    }

    public function templateFields()
    {
        $templateFields = $this->repository->getTemplateFields();

        return response()->json(
            $templateFields
        );
    }

    public function templateVersionHistory($templateId)
    {
        $templateVersionHistories = $this->repository->getTemplateVersionHistory($templateId);

        foreach ($templateVersionHistories as $templateVersionHistory) {
            $createdAt = Carbon::parse($templateVersionHistory->created_at);
            $numberOfDays = $createdAt->diffInDays(Carbon::now());

            $user = User::find($templateVersionHistory->user_id);

            $userName = $user ? $user->name : 'Unknown User';

            $data[] = [
                'id' => $templateVersionHistory->id,
                'template_id' => $templateVersionHistory->template_id,
                'action' => ucfirst($templateVersionHistory->action),
                'activity' => $templateVersionHistory->activity,
                'user' => $userName,
                'date' => $createdAt->format('d/m/Y'),
                'days_ago' => $numberOfDays,
                'content' => $templateVersionHistory->content
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        $employee = Employee::where('user_id', auth()->user()->id)->first(); // owner_employee_id , 

        $ownerEmployeeId = $employee->id;

        return response()->json($ownerEmployeeId);
    }

    public function store(StoreRequest $request)
    {
        //$ownerEmployeeId = $request->get('owner_employee_id');
        $filename = $request->get('filename');
        $content = $request->get('content');
        $type = $request->get('type');
        $status = $request->get('status');
        $fieldNames = $request->get('field_name');
        $fieldData = $request->get('field_type');

        $userId = auth()->user()->id;
        $user = User::where('id', $userId)->with('employee')->firstOrFail();
        $employeeId = $user->employee->id;

        $action = 'created';
        $activity = $user->name . ' ' . $action . ' this template';

        // Make sure to run >php artisan storage:link once
        // Handle screenshot upload
        $template = null;
        if ($request->hasFile('screenshot')) {
            $image = $request->file('screenshot');
            $imageName = 'template_screenshot_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('templates/screenshots', $imageName, 'public');
            $template = $this->repository->store(
                $employeeId,
                $filename,
                $content,
                $status,
                $type,
                $path
            );
        }

        $this->storeHistory($template->id, $content, $activity, $userId);

        return response()->json([
            'templateID' => $template->id,
        ]);

    }

    private function storeHistory($templateId, $content, $activity, $userId): void
    {
        $action = 'updated';

        $this->repository->storeTemplateVersionHistory(
            $templateId,
            $content,
            $action,
            $activity,
            $userId
        );
    }

    public function edit($id)
    {
        $data['template'] = $this->repository->getTemplateById($id);

        $data['templateField'] = $this->repository->getTemplateFieldsById($id);

        return response()->json($data);
    }

    public function updateTemplate(Request $request, $id)
    {
        try {
            $template = $this->repository->getTemplateById($id);

            if (!$template) {
                return response()->json(['error' => 'Template not found'], 404);
            }

            $filename = $request->get('filename');
            $content = $request->get('content');
            $status = $request->get('status');
            $currentScreenShot = $request->file('screenshot');
            $prevScreenshot = $template->getAttribute('screenshot');

            $userId = auth()->user()->id;
            $user = User::where('id', $userId)->firstOrFail();
            $action = 'updated';
            $activity = $user->name . ' ' . $action . ' this template';
            $templateId = $template->getAttribute('id');

            //screenshot processing
            //TODO: Delete previous screenshot - to prevent cloging of storage
            // Delete the file

            if (!empty($prevScreenshot) && Storage::disk('public')->exists($prevScreenshot)) {
                Storage::disk('public')->delete($prevScreenshot);
            }

            $imageName = 'template_screenshot_' . time() . '.' . $currentScreenShot->getClientOriginalExtension();
            $path = $currentScreenShot->storeAs('templates/screenshots', $imageName, 'public');

            $updatedTemplate = $this->repository->updateTemplate(
                $templateId,
                $path,
                $filename,
                $content,
                $status
            );

            $this->storeHistory($templateId, $content, $activity, $userId);

            return response()->json(['message' => 'Template updated successfully', 'data' => $template], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update template', 'message' => $e->getMessage()], 500);
        }



        $existingFields = $this->repository->getTemplateFieldByTemplateId($templateId);

        $existingFieldIds = $existingFields->pluck('id')->toArray();

        foreach ($fieldNames as $index => $fieldName) {
            $active = isset($actives[$index]) ? $actives[$index] : 1;

            if (isset($existingFieldIds[$index])) {
                $this->repository->updateTemplateField(
                    $existingFieldIds[$index],
                    $template->id,
                    $fieldName,
                    $index
                );
            } else {
                $this->repository->storeTemplateField(
                    $template->id,
                    $fieldName,
                    $index
                );
            }
        }

        $this->repository->storeTemplateVersionHistory(
            $template->id,
            $content,
            $action,
            $activity,
            $userId
        );

        return response()->json($updatedTemplate, 200);
    }

    public function updateTemplateStatus(Request $request, $id)
    {
        $template =  $this->repository->getTemplateById($id);
        $templateId = $template->id;

        $status = $request->get('status');

        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->firstOrFail();

        $action = 'updated';

        $activity = $user->name . ' ' . $status . ' this template';

        $updatedTemplate = $this->repository->updateTemplateStatus(
            $templateId,
            $status
        );

        $this->repository->storeTemplateVersionHistory(
            $template->id,
            $template->content,
            $action,
            $activity,
            $userId
        );

        return response()->json($updatedTemplate, 200);
    }

    public function deleteTemplate(string $id)
    {
        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->firstOrFail();

        $action = 'deleted';

        $activity = $user->name . ' ' . $action . ' this template';

        $template = $this->repository->getTemplateById($id);

        $this->repository->deleteTemplateById($id);

        $this->repository->storeTemplateVersionHistory(
            $template->id,
            $template->content,
            $action,
            $activity,
            $userId
        );

        return response()->json(['deleted'], 201);
    }

    public function sharedTemplate()
    {
        $sharedTemplate = $this->repository->getSharedTemplate();

        return response()->json(
            $sharedTemplate
        );
    }

    public function creatSharedTemplate($templateId)
    {
        $data['sharedTemplate'] = $this->repository->getTemplateById($templateId);

        $employee = Employee::where('user_id', auth()->user()->id)->first();

        $data['sharedByEmployeeId'] = $employee->id;

        return response()->json(
            $data
        );
    }

    public function storeSharedTemplate(StoreSharedRequest $request, $templateId)
    {
        $userId = auth()->user()->id;

        $employee = Employee::where('user_id', $userId)->first();

        $ownerEmployeeId = $request->get('owner_employee_id');

        $template = $this->repository->getTemplateById($templateId);

        $sharedTemplate = $this->repository->storeSharedTemplate(
            $template->id,
            $ownerEmployeeId,
            $employee->id,
        );

        return response()->json([
            'sharedTemplate' => $sharedTemplate->id,
        ]);
    }

    public function deleteSharedTemplate(string $id)
    {
        $this->repository->getSharedTemplateById($id);

        $this->repository->deleteSharedTemplateById($id);

        return response()->json(['deleted'], 201);
    }
}
