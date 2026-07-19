<?php

namespace App\Core\v1\Resource\Http\Controllers;

use App\Core\v1\Resource\Models\Resource;
use App\Core\v1\Resource\Repositories\ResourceRepository;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResourceController extends AppController
{
    public function __construct(
        protected $repository = new ResourceRepository
    )
    {
        parent::__construct();
    }

    // public function serveResource($filename)
    // {
    //     $path = storage_path('app/public/resources/' . $filename);

    //     if (!file_exists($path)) {
    //         abort(404);
    //     }

    //     // Get file extension
    //     $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    //     // Map extensions to mime types
    //     $mimeTypes = [
    //         'pdf'  => 'application/pdf',
    //         'doc'  => 'application/msword',
    //         'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    //         'xls'  => 'application/vnd.ms-excel',
    //         'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    //         'ppt'  => 'application/vnd.ms-powerpoint',
    //         'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    //         'jpg'  => 'image/jpeg',
    //         'jpeg' => 'image/jpeg',
    //         'png'  => 'image/png',
    //         'gif'  => 'image/gif',
    //         // Add more as needed
    //     ];

    //     // Use detected or fallback mime
    //     $contentType = $mimeTypes[$ext] ?? mime_content_type($path);

    //     return response()->file($path, [
    //         'Content-Type' => $contentType,
    //         'Content-Disposition' => 'inline; filename="'.$filename.'"'
    //     ])->withHeaders([
    //         'Cache-Control' => 'no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0',
    //         'Pragma'        => 'no-cache',
    //         'Expires'       => '0',
    //         'Surrogate-Control' => 'no-store',
    //     ]);
    // }


    public function serveResource($filename)
    {
        $safeFilename = basename($filename);
        $filePath = 'public/resources/' . $safeFilename;

        if (!\Storage::exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Guess MIME type
        $mimeType = \Storage::mimeType($filePath);

        // Prevent caching
        $headers = [
            'Cache-Control'     => 'no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0',
            'Pragma'            => 'no-cache',
            'Expires'           => '0',
            'Surrogate-Control' => 'no-store',
            'Content-Type'      => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$safeFilename.'"', // <- THIS IS IMPORTANT
        ];

        // Output file inline
        return response()->file(storage_path('app/' . $filePath), $headers);
    }



    public function index(Request $request)
    {
        $user        = auth()->user();
        $userId      = $user->id;
        $employee    = $user->employee ?? null;
        $employeeId  = $employee ? $employee->id : null;
        $userRoleId  = $user->role_id ?? null;

        $categoryId    = $request->get('category');
        $searchtext    = $request->get('searchtext');
        $perPage       = $request->get('per_page', 10);
        $page          = $request->get('page', 1);
        $divisionId    = $request->get('division');
        $jobTitleId    = $request->get('job_title');
        $dueDate       = $request->get('due_date');
        $roleArr       = $request->get('role', []);
        $privacyStatus = $request->get('privacy_status');
        $creatorRoleId = (is_array($roleArr) && isset($roleArr['value']) && $roleArr['value']) ? $roleArr['value'] : null;

        $sortBy        = $request->get('sort_by', 'id');
        $sortDirection = strtolower($request->get('sort_direction', 'asc'));

        // Sanitize allowed sorts
        $allowedSorts = [
            'id', 'name', 'description', 'created_at', 'type', 'status', 'due_date', 'resource_tag_name', 'extension'
        ];
        $sortBySafe        = in_array($sortBy, $allowedSorts) ? $sortBy : 'id';
        $sortDirectionSafe = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

        $query = \App\Core\v1\Resource\Models\Resource::query()
            ->with(['tags', 'category', 'createdBy', 'employees', 'roles']);

        $query->where(function ($q) use ($userId, $employeeId, $userRoleId) {
            $q->where('created_by', $userId)
                ->orWhereIn('status', ['public', 'everyone'])
                ->orWhere(function ($q2) use ($userId) {
                    $q2->where('status', 'only me')
                        ->where('created_by', $userId);
                })
                ->orWhere(function ($q3) use ($employeeId) {
                    // USER PRIVACY: Assigned by employee_id
                    $q3->where('status', 'user')
                        ->whereHas('employees', function ($q4) use ($employeeId) {
                            $q4->where('employees.id', $employeeId);
                        });
                })
                ->orWhere(function ($q5) use ($userRoleId) {
                    // ROLE PRIVACY: Assigned by user.role_id
                    $q5->where('status', 'role')
                        ->whereHas('roles', function ($q6) use ($userRoleId) {
                            $q6->where('roles.id', $userRoleId);
                        });
                });
        });


        // --- FILTERS ---
        if ($categoryId) {
            $query->where('resource_category_id', $categoryId);
        }
        if ($searchtext) {
            $query->where('name', 'LIKE', '%' . $searchtext . '%');
        }
        if ($divisionId) {
            $query->whereHas('createdBy.employee', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }
        if ($privacyStatus) {
            $query->where('status', $privacyStatus);
        }        
        if ($jobTitleId) {
            $query->whereHas('createdBy.employee', function ($q) use ($jobTitleId) {
                $q->where('position_id', $jobTitleId);
            });
        }
        if ($dueDate) {
            if (strpos($dueDate, 'T') !== false) {
                $dueDate = substr($dueDate, 0, 10);
            }
            $query->whereDate('resources.due_date', $dueDate);
        }
        if ($creatorRoleId) {
            $query->whereHas('createdBy.roles', function ($q) use ($creatorRoleId) {
                $q->where('id', $creatorRoleId);
            });
        }

        // --- SORTING ---
        if ($sortBySafe === 'resource_tag_name') {
            $query->with(['tags' => function ($q) {
                $q->orderBy('name');
            }])->orderByRaw('(SELECT name FROM resource_tags 
                INNER JOIN resource_tag ON resource_tags.id = resource_tag.resource_tag_id 
                WHERE resource_tag.resource_id = resources.id LIMIT 1) ' . $sortDirectionSafe);
        } elseif ($sortBySafe === 'extension') {
            $query->orderBy('extension', $sortDirectionSafe);
        } else {
            $query->orderBy($sortBySafe, $sortDirectionSafe);
        }

        // --- PAGINATION ---
        if ($perPage === 'All') {
            $results = $query->get();
            $total   = $results->count();
            $perPageCount = $total;
            $currentPage  = 1;
        } else {
            $paginator = $query->paginate((int) $perPage, ['*'], 'page', (int) $page);
            $results   = $paginator->items();
            $total     = $paginator->total();
            $perPageCount = $paginator->perPage();
            $currentPage  = $paginator->currentPage();
        }

        return response()->json([
            'data'         => $results,
            'total'        => $total,
            'current_page' => $currentPage,
            'per_page'     => $perPageCount,
            'total_pages'  => $perPageCount > 0 ? ceil($total / $perPageCount) : 1,
            'user_role_id' => $userRoleId,
        ]);
    }

    public function indexAcknowledgementRequired(Request $request)
    {
        $user = auth()->user();
        $isSuperUser = strtolower($user->role->name ?? '') === 'super user';

        if ($isSuperUser) {
            return $this->indexAcknowledgementRequiredSuperAdmin($request, $user);
        } else {
            return $this->indexAcknowledgementRequiredUser($request, $user);
        }
    }

    protected function getCommonResourceQuery(Request $request, $user)
    {
        // Parse filters just like before
        $categoryArr = $request->get('category', []);
        $categoryId = '';
        if (is_array($categoryArr) && isset($categoryArr['category'])) {
            $categoryId = $categoryArr['category'];
        } elseif (!is_array($categoryArr) && $categoryArr) {
            $categoryId = $categoryArr;
        }
        $divisionId   = is_array($categoryArr) && isset($categoryArr['division']) ? $categoryArr['division'] : '';
        $jobTitleId   = is_array($categoryArr) && isset($categoryArr['job_title']) ? $categoryArr['job_title'] : '';
        $status       = is_array($categoryArr) && isset($categoryArr['status']) ? $categoryArr['status'] : '';
        $dueDate      = is_array($categoryArr) && isset($categoryArr['due_date']) ? $categoryArr['due_date'] : '';
        $creatorRoleId = is_array($categoryArr) && isset($categoryArr['creator_role_id']) ? $categoryArr['creator_role_id'] : $request->get('creator_role_id', null);

        $perPage = is_array($categoryArr) && isset($categoryArr['per_page']) ? $categoryArr['per_page'] : $request->get('per_page', 10);
        $page = is_array($categoryArr) && isset($categoryArr['page']) ? $categoryArr['page'] : $request->get('page', 1);

        // Get role filter (ONLY from categoryArr['role']['value'])
        $roleId = null;
        if (is_array($categoryArr) && isset($categoryArr['role']['value']) && $categoryArr['role']['value']) {
            $roleId = $categoryArr['role']['value'];
        }

        $searchtext   = $request->get('searchtext');

        $sortBy = '';
        $sortDir = 'asc';
        if (is_array($categoryArr)) {
            $sortBy = $categoryArr['sort_by'] ?? $request->get('sort_by', '');
            $sortDir = $categoryArr['sort_direction'] ?? $request->get('sort_direction', 'asc');
        } else {
            $sortBy = $request->get('sort_by', '');
            $sortDir = $request->get('sort_direction', 'asc');
        }
        $sortBy = $sortBy ?: 'id';
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        $allowedDbSorts = [
            'id','name','description','due_date','created_by','created_at','status','type','resource_tag_name','color'
        ];
        $isVirtualSort = $sortBy === 'is_acknowledged';

        $select = [
            'resources.*',
            'employee_resource.employee_id',
            'employees.first_name',
            'employees.last_name',
            'users.role_id as employee_role_id',
            'users.id as employee_user_id'
        ];
        $select[] = 'resource_tags.name as resource_tag_name';
        $select[] = 'resource_tags.color as color';

        $query = Resource::query()
            ->select($select)
            ->distinct()
            ->leftJoin('resource_tag', 'resource_tag.resource_id', '=', 'resources.id')
            ->leftJoin('resource_tags', 'resource_tags.id', '=', 'resource_tag.resource_tag_id')
            ->leftJoin('employee_resource', 'employee_resource.resource_id', '=', 'resources.id')
            ->leftJoin('employees', 'employee_resource.employee_id', '=', 'employees.id')
            ->leftJoin('users', 'employees.user_id', '=', 'users.id')
            ->leftJoin('resource_role', 'resource_role.resource_id', '=', 'resources.id')
            ->where('resources.acknowledgement_required', true);

        // Role filter (for filter panel)
        if (!is_null($roleId)) {
            $query->where('users.role_id', $roleId);
        }

        // Category filter
        if ($categoryId) {
            if (is_array($categoryId)) {
                $query->whereIn('resource_category_id', $categoryId);
            } else {
                $query->where('resource_category_id', $categoryId);
            }
        }
        if ($searchtext) {
            $query->where('resources.name', 'LIKE', '%' . $searchtext . '%');
        }
        if ($divisionId) {
            $query->where('employees.division_id', $divisionId);
        }
        if ($jobTitleId) {
            $query->where('employees.position_id', $jobTitleId);
        }

        $searchtext = '';
        if (is_array($categoryArr) && isset($categoryArr['search']) && $categoryArr['search']) {
            $searchtext = $categoryArr['search'];
        }

        // --- SEARCH FILTER ---
        if ($searchtext) {
            $query->where(function ($q) use ($searchtext) {
                $q->where('resources.name', 'LIKE', "%$searchtext%");
                // add more fields if needed
            });
        }        
        $tz = 'Australia/Sydney';
        if ($dueDate) {
            // $query->whereDate('resources.due_date', $dueDate);
            $startOfDay = \Carbon\Carbon::createFromFormat('Y-m-d', $dueDate, $tz)->startOfDay()->setTimezone('UTC');
            $endOfDay   = \Carbon\Carbon::createFromFormat('Y-m-d', $dueDate, $tz)->endOfDay()->setTimezone('UTC');

            $query->whereBetween('resources.due_date', [
                $startOfDay->toDateTimeString(),
                $endOfDay->toDateTimeString(),
            ]);        
        }
        if ($creatorRoleId) {
            $query->whereHas('createdBy.roles', function($q) use ($creatorRoleId) {
                $q->where('roles.id', $creatorRoleId);
            });
        }

        // Sorting
        if (!$isVirtualSort && in_array($sortBy, $allowedDbSorts)) {
            if ($sortBy === 'resource_tag_name') {
                $query->orderBy('resource_tags.name', $sortDir);
            } elseif ($sortBy === 'color') {
                $query->orderBy('resource_tags.color', $sortDir);
            } elseif ($sortBy === 'type') {
                $query->orderBy('resources.type', $sortDir);
            } else {
                $query->orderBy("resources.$sortBy", $sortDir);
            }
        }

        return [$query, $perPage, $page, $status, $isVirtualSort, $sortDir, $sortBy];
    }

    // -----------------------------
    // Super Admin version
    // -----------------------------
    public function indexAcknowledgementRequiredSuperAdmin(Request $request)
    {
        // 1. Build base query & filters (just like your old logic)
        list($query, $perPage, $page, $status, $isVirtualSort, $sortDir, $sortBy) = $this->getCommonResourceQuery($request, auth()->user());
        $resources = $query->get();

        // 2. Build all employees, roles, and users lookups
        $allEmployees = \App\Models\Employee::with('user')->get()->keyBy('id');
        $allUsers = \App\Models\User::with('employee')->get()->keyBy('id');
        $allRoles = \App\Models\Role::get()->keyBy('id');

        // 3. Build resource->users mapping
        $flattened = [];
        foreach ($resources as $resource) {
            // For super admin, always show ALL employees as receivers
            // $resourceReceivers = $allEmployees->unique('id');
        $resourceReceivers = collect();
        if ($resource->status === 'everyone') {
            $resourceReceivers = $allEmployees;
        } elseif ($resource->status === 'user' && !empty($resource->employees)) {
            foreach ($resource->employees as $emp) {
                if ($allEmployees->has($emp['id'])) {
                    $resourceReceivers->put($emp['id'], $allEmployees[$emp['id']]);
                }
            }
        } elseif ($resource->status === 'role' && !empty($resource->roles)) {
            foreach ($resource->roles as $role) {
                $roleId = is_object($role) ? $role->id : $role;
                foreach ($allEmployees as $emp) {
                    if ($emp->user && $emp->user->role_id == $roleId) {
                        $resourceReceivers->put($emp->id, $emp);
                    }
                }
            }
        }

            // 4. For each receiver, build flattened row
            foreach ($resourceReceivers as $employee) {
                $user = $employee->user;
                if (!$user) continue;
                $ack = \DB::table('acknowledgement')
                    ->where('resource_id', $resource->id)
                    ->where('user_id', $employee->user_id)
                    ->first();

                $flattened[] = [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'resource_status' => $resource->status,
                    'due_date' => $resource->due_date,
                    'type' => $resource->type,
                    'path' => $resource->path,
                    'description' => $resource->description,
                    'resource_tag_name' => $resource->resource_tag_name ?? null,
                    'mime_type' => $resource->mime_type,
                    'extension' => $resource->extension,
                    // ...add more resource fields as needed
                    'created_at' => $resource->created_at,
                    'user_id' => $user->id,
                    'employee_id' => $employee->id,
                    'acknowledged_by' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
                    'is_acknowledged' => !!$ack,
                    'ack_id' => $ack->id ?? null,
                    'acknowledged_at' => $ack->created_at ?? null,
                    // ...add more as needed
                ];
            }
        }

        // 5. Sorting and filtering on the full flattened set
        $collection = collect($flattened);

        // -- Sort: By is_acknowledged (virtual) or others
        if ($sortBy === 'acknowledged_by') {
            $collection = $collection->sortBy(function($item) {
                return strtolower($item['acknowledged_by'] ?? '');
            }, SORT_REGULAR, $sortDir === 'desc')->values();
        }
        elseif ($isVirtualSort) {
            $collection = $collection->sortBy('is_acknowledged', SORT_REGULAR, $sortDir === 'desc');
        }
        // Optionally: sort by resource_name etc.
        // $collection = $collection->sortBy('resource_name');

        // -- Status filter
        if ($status === 'acknowledged') {
            $collection = $collection->where('is_acknowledged', true)->values();
        } elseif ($status === 'not_viewed') {
            $collection = $collection->where('is_acknowledged', false)->values();
        }

        // 6. Pagination (AFTER flattening and filtering)
        $total = $collection->count();
        $totalPages = $perPage === 'All' ? 1 : (int)ceil($total / $perPage);
        $currentPage = (int)$page;
        $perPage = $perPage === 'All' ? $total : (int)$perPage;

        $paged = $perPage === 'All'
            ? $collection
            : $collection->forPage($currentPage, $perPage)->values();

        return response()->json([
            'data'         => $paged,
            'total'        => $total,
            'current_page' => $currentPage,
            'per_page'     => $perPage,
            'total_pages'  => $totalPages,
            'is_super_admin' => true,
        ]);
    }



    // -----------------------------
    // Regular User version
    // -----------------------------
public function indexAcknowledgementRequiredUser(Request $request, $user)
{
    // Get IDs
    $userId      = $user->id;
    $employeeId  = $user->employee_id ?? ($user->employee->id ?? null);
    $userRoleId  = $user->role_id ?? ($user->employee->role_id ?? null);

    // Get filters and pagination
    list($query, $perPage, $page, $status, $isVirtualSort, $sortDir, $sortBy) = $this->getCommonResourceQuery($request, $user);

    // CRITICAL: Handle "everyone", "role", "user" assignments
    $query->where(function ($q) use ($userId, $userRoleId, $employeeId) {
        // 1. Resources for everyone
        $q->where('resources.status', 'everyone');
        // 2. Resources created by this user
        $q->orWhere('resources.created_by', $userId);

        // 3. Resources shared directly with user (employee_resource)
        if ($employeeId) {
            $q->orWhere(function ($sub) use ($employeeId) {
                $sub->where('resources.status', 'user')
                    ->whereExists(function ($query) use ($employeeId) {
                        $query->select(\DB::raw(1))
                              ->from('employee_resource')
                              ->whereColumn('employee_resource.resource_id', 'resources.id')
                              ->where('employee_resource.employee_id', $employeeId);
                    });
            });
        }

        // 4. Resources shared with this role (resource_role)
        if ($userRoleId) {
            $q->orWhere(function ($sub) use ($userRoleId) {
                $sub->where('resources.status', 'role')
                    ->whereExists(function ($query) use ($userRoleId) {
                        $query->select(\DB::raw(1))
                              ->from('resource_role')
                              ->whereColumn('resource_role.resource_id', 'resources.id')
                              ->where('resource_role.role_id', $userRoleId);
                    });
            });
        }
    });

    // [Optional: Debug output!]
    // \Log::info(['sql' => $query->toSql(), 'bindings' => $query->getBindings(), 'employeeId' => $employeeId, 'userId' => $userId, 'userRoleId' => $userRoleId]);

    // Fetch rows
    $allResources = $query->get();

    // Get resource IDs for this page
    $resourceIds = $allResources->pluck('id')->all();

    // Fetch all ack for this user
    $acknowledgements = \DB::table('acknowledgement')
        ->whereIn('resource_id', $resourceIds)
        ->where('user_id', $userId)
        ->get()
        ->keyBy('resource_id');

    // Attach ack to resources
    foreach ($allResources as $resource) {
        $ack = $acknowledgements[$resource->id] ?? null;
        $resource->is_acknowledged = !!$ack;
        $resource->ack_id = $ack->id ?? null;
        $resource->acknowledged_at = $ack->created_at ?? null;
    }

    // Status filtering (acknowledged/not_viewed)
    if ($status === 'acknowledged') {
        $allResources = $allResources->filter(function ($item) {
            return $item->is_acknowledged === true;
        })->values();
    } elseif ($status === 'not_viewed') {
        $allResources = $allResources->filter(function ($item) {
            return $item->is_acknowledged === false;
        })->values();
    }

    // Virtual sorting
    if ($isVirtualSort) {
        $allResources = $allResources->sortBy(function ($item) {
            return $item->is_acknowledged ? 1 : 0;
        }, SORT_REGULAR, $sortDir === 'desc')->values();
    }

    // Pagination (forPage is 1-based)
    $total       = $allResources->count();
    $totalPages  = $perPage === 'All' ? 1 : (int)ceil($total / $perPage);
    $currentPage = (int)$page;
    $page        = max(1, $currentPage);
    $perPage     = $perPage === 'All' ? $total : (int)$perPage;

    $pagedResources = $perPage === 'All'
        ? $allResources
        : $allResources->forPage($page, $perPage)->values();

    // Return response
    return response()->json([
        'data'           => $pagedResources,
        'total'          => $total,
        'current_page'   => $currentPage,
        'per_page'       => $perPage,
        'total_pages'    => $totalPages,
        'is_super_admin' => false,
        'userId'         => $userId,
        'employeeId'     => $employeeId,
    ]);
}





    public function categories()
    {
        return response()->json(
            $this->repository->categories()
        );
    }

    public function storeCategory(Request $request)
    {
        $name = $request->get('name');
        $imagePath = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $pathExt = $this->repository->uploadResource($image);
            $imagePath = $pathExt['path'];
        }

        $category = $this->repository->createCategory($name, null, $imagePath);

        return response()->json($category);
    }
    
    public function storeTag(Request $request)
    {
        $name = $request->get('name');
        $color = $request->get('color');
        $tag = $this->repository->createTag($name, $color);
        return response()->json($tag);
    }

    public function store(Request $request)
    {
        $file = $request->file('file');
        $name = $request->get('name');
        $assignedto = $request->get('id');
        $description = $request->get('description');
        $type = $request->get('type');
        $link = $request->get('link');
        $ownedBy = $request->get('owned_by');
        $categoryId = $request->get('category');
        $resourceTagId = $request->get('tagid');
        $privacy = $request->get('privacy') ?? 1;
        $due_date = $request->get('due_date');
        $path = null;
        $extension = null;
        $mimeType = null;
        $acknowledgementRequired = $request->get('acknowledgement_required', false);

        // Accept users (employees) as JSON stringified arrays from frontend
        $users = json_decode($request->get('users', '[]'), true) ?? [];
        $roles = json_decode($request->get('roles', '[]'), true) ?? [];

        $requestArray = $request->toArray();

        if ($type == Resource::TYPE_FILE && $request->hasFile('file')) {
            $pathExt = $this->repository->uploadResource($file);
            $path = $pathExt['path'];
            $extension = $pathExt['extension'];
            $mimeType = $pathExt['mimeType'];
        }

        $resource = $this->repository->store(
            $categoryId,
            $name,
            $path,
            $type,
            $mimeType,
            $ownedBy,
            $extension,
            $description,
            $link,
            $privacy,
            $acknowledgementRequired,
            $due_date
        );

        if($resourceTagId !== "undefined"){
            $resourceKey = $resource->getKey();
            $resource_tag_id = $requestArray['tagid'];
            DB::table("resource_tag")->insert([
                'resource_id' => $resourceKey,
                'resource_tag_id' => $resource_tag_id
            ]);
        }

        // ====== SYNC EMPLOYEES TO employee_resource TABLE ======
        if ($privacy == 2 && count($users)) {
            DB::table('employee_resource')->where('resource_id', $resource->id)->delete();
            $insertData = [];
            foreach ($users as $user) {
                // $user is array/object, extract employee_id
                $employeeId = null;
                if (is_array($user)) {
                    $employeeId = $user['employee_id'] ?? $user['id'] ?? null;
                } elseif (is_object($user)) {
                    $employeeId = $user->employee_id ?? $user->id ?? null;
                }
                if ($employeeId) {
                    $insertData[] = [
                        'resource_id' => $resource->id,
                        'employee_id' => $employeeId
                    ];
                }
            }
            if (count($insertData)) {
                DB::table('employee_resource')->insert($insertData);
            }
        }

        // ====== SYNC ROLES (if still needed) ======
        if ($privacy == 5 && count($roles)) {
            DB::table('resource_role')->where('resource_id', $resource->id)->delete();
            $insertData = [];
            foreach ($roles as $role) {
                $roleId = is_array($role) ? ($role['id'] ?? null) : (is_object($role) ? ($role->id ?? null) : $role);
                if ($roleId) {
                    $insertData[] = [
                        'resource_id' => $resource->id,
                        'role_id' => $roleId
                    ];
                }
            }
            if (count($insertData)) {
                DB::table('resource_role')->insert($insertData);
            }
        }

        return response()->json([
            'resource' => $resource
        ]);
    }


public function update(Request $request, $id)
{
    $file = $request->file('file');
    $name = $request->get('name');
    $description = $request->get('description');
    $type = $request->get('type');
    $link = $request->get('link');
    $ownedBy = $request->get('owned_by');
    $categoryId = $request->get('category');
    $resourceTagId = $request->get('tagid');
    $privacy = intval($request->get('privacy', 1)); // force int!
    $due_date = $request->get('due_date');
    $acknowledgementRequired = $request->get('acknowledgement_required', false);

    // Accept users/roles as JSON stringified arrays from frontend
    $users = json_decode($request->get('users', '[]'), true) ?? [];
    $roles = json_decode($request->get('roles', '[]'), true) ?? [];

    // LOG what you're receiving for debugging
    \Log::info([
        'privacy' => $privacy,
        'users' => $users,
        'users_raw' => $request->get('users'),
        'roles' => $roles,
        'roles_raw' => $request->get('roles'),
    ]);

    // Find resource
    $resource = $this->repository->find($id);
    if (!$resource) {
        return response()->json(['message' => 'Resource not found'], 404);
    }

    // === Determine path, extension, and mimeType for update ===
    if ($type == \App\Core\v1\Resource\Models\Resource::TYPE_FILE) {
        if ($request->hasFile('file')) {
            // New file uploaded
            $pathExt = $this->repository->uploadResource($file);
            $path = $pathExt['path'];
            $extension = $pathExt['extension'];
            $mimeType = $pathExt['mimeType'];
        } else {
            // Use old file if no new file uploaded
            $path = $resource->path;
            $extension = $resource->extension;
            $mimeType = $resource->mime_type;
        }
    } else {
        // For link resources, just use the link field
        $path = $link;
        $extension = pathinfo($link, PATHINFO_EXTENSION);
        $mimeType = null;
    }

    // Update the resource (make sure status matches privacy)
    $updatedResource = $this->repository->update(
        $resource,
        (int) $categoryId,
        $name,
        $path,
        $type,
        $mimeType,
        $ownedBy,
        $extension,
        $description,
        $link,
        $privacy,
        $acknowledgementRequired,
        $due_date
    );

    // Sync tags (assumes single tag ID for simplicity)
    \DB::table('resource_tag')->where('resource_id', $id)->delete();
    if ($resourceTagId && $resourceTagId !== "undefined") {
        \DB::table('resource_tag')->insert([
            'resource_id' => $id,
            'resource_tag_id' => $resourceTagId
        ]);
    }

    // ====== SYNC SHARING ======
    \DB::table('employee_resource')->where('resource_id', $id)->delete();
    \DB::table('resource_role')->where('resource_id', $id)->delete();

    // Force "users" to be array of employee IDs (even if objects)
    $userIds = [];
    foreach ($users as $user) {
        if (is_array($user) && isset($user['id'])) {
            $userIds[] = $user['id'];
        } else if (is_numeric($user)) {
            $userIds[] = $user;
        }
    }
    $userIds = array_filter(array_unique($userIds));

    if ($privacy === 2 && !empty($userIds)) {
        $validEmployeeIds = \DB::table('employees')
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->toArray();

        $insertData = [];
        foreach ($validEmployeeIds as $employeeId) {
            $insertData[] = [
                'resource_id' => $id,
                'employee_id' => $employeeId,
            ];
        }
        if ($insertData) {
            \DB::table('employee_resource')->insert($insertData);
            \Log::info(['insert_employee_resource' => $insertData]);
        }
    }

    if ($privacy === 5 && count($roles)) {
        $roleIds = [];
        foreach ($roles as $role) {
            if (is_array($role) && isset($role['id'])) $roleIds[] = $role['id'];
            else if (is_numeric($role)) $roleIds[] = $role;
        }
        $roleIds = array_filter(array_unique($roleIds));

        $insertData = [];
        foreach ($roleIds as $roleId) {
            $insertData[] = [
                'resource_id' => $id,
                'role_id' => $roleId,
            ];
        }
        if ($insertData) {
            \DB::table('resource_role')->insert($insertData);
            \Log::info(['insert_resource_role' => $insertData]);
        }
    }

    return response()->json([
        'resource' => $updatedResource
    ]);
}





public function updatePrivacy(Request $request, $id)
{
    // Validate the request
    $validated = $request->validate([
        'status' => 'required|string',
        'employees' => 'nullable|array',
        'employees.*.id' => 'required|integer',
        'roles' => 'nullable|array',
        'roles.*.id' => 'required|integer',
    ]);

    // Find the resource or fail
    $resource = \App\Core\v1\Resource\Models\Resource::findOrFail($id);

    // Update status
    $resource->status = $validated['status'];
    $resource->save();

    // Sync employees (employee_resource pivot)
    if (isset($validated['employees'])) {
        $employeeIds = collect($validated['employees'])->pluck('id')->toArray();
        $resource->employees()->sync($employeeIds);
    } else {
        // If empty or not set, detach all
        $resource->employees()->detach();
    }

    // Sync roles (resource_role pivot)
    if (isset($validated['roles'])) {
        $roleIds = collect($validated['roles'])->pluck('id')->toArray();
        $resource->roles()->sync($roleIds);
    } else {
        // If empty or not set, detach all
        $resource->roles()->detach();
    }

    // Optionally, reload fresh relations
    $resource->load('employees', 'roles');

    return response()->json([
        'message' => 'Resource privacy updated successfully.',
        'resource' => $resource,
        'status' => $resource->status,
    ]);
}

    
    

    public function getTagsByResource($id)
    {
        $tagIds = DB::table('resource_tag')
        ->where('resource_id', $id)
        ->pluck('resource_tag_id');

        return response()->json([
            'tags' => $tagIds
        ]);
    } 

    public function delete($id)
    {
        $resource = $this->repository->find($id);

        if (!empty($resource)) {
            $this->repository->use($resource)->delete();
        }

        return response()->json([
            'message' => 'Resource deleted!'
        ], 201);
    }
    
    
    public function fetchTags() {
        return response()->json(
            $this->repository->tags()
        );
        
       
    }
}
