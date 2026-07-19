<?php

namespace App\Http\Controllers;

use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Models\Notes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    protected NotificationRepository $notification;

    public function __construct()
    {
        $this->notification = new NotificationRepository;
    }

    //
    public function create(Request $request)
    {
        $data = $request->all();
        if ($request->hasFile('file')) {
            $file      = $request->file('file');
            $file_name = Str::uuid() . '.' . $file->extension();
            $data['file'] = $file_name;
            $data['file_name'] = $file->getClientOriginalName();
            $request->file->storeAs('public/attachments/notes', $file_name);
        }
        Notes::create($data);

        try {
            $user = User::find($request->get('user_id'));
            // Manager, HR
            $this->notification
                ->from($user)
                ->toHr()
                ->toManagers()
                ->notify(
                    "New Note created.",
                    "<b>{$user->employee->first_name} {$user->employee->first_name}</b> has created a new note."
                );
            // User
            // Manager, HR
            $this->notification
                ->from($user)
                ->to([$user->id])
                ->notify(
                    "New Note created.",
                    "You have created a new note."
                );
        } catch (\Exception $e) {

        }


        return response()->json(array('message' => "Note successfully saved"));
    }

    public function update(Request $request)
    {
        $data = $request->all();
        if ($request->hasFile('file')) {
            $note = Notes::where('id', $data['id'])->first();
            $path = storage_path() . '/app/public/attachments/notes/' . $note->file;
            if ($note->file && File::exists($path)) {
                array_map('unlink', glob($path));
            }

            $file      = $request->file('file');
            $file_name = Str::uuid() . '.' . $file->extension();
            $data['file'] = $file_name;
            $data['file_name'] = $file->getClientOriginalName();
            $request->file->storeAs('public/attachments/notes', $file_name);
        }
        Notes::where('id', $data['id'])->update($data);

        return response()->json(array('message' => "Note successfully updated"));
    }

    public function userNotes(Request $request)
    {
        $limit = 5;
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $limit;

        $user = User::where('id', $request->id)->with('role')->first();

        $query = Notes::query()->with('user.employee');

        $query->where(function ($query) use ($request, $user) {
            $query->where('user_id', $request->id);

            if ($user->role && $user->role->name === 'HR') {
                $query->orWhere('tag', 'HR Only');
            } else if ($user->role && $user->role->name === 'Finance') {
                $query->orWhere('tag', 'Finance Only');
            }
        });

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $q->where('subject', 'like', $searchTerm)
                    ->orWhere('subject_title', 'like', $searchTerm)
                    ->orWhereHas('user.employee', function ($query) use ($searchTerm) {
                        $query->where('first_name', 'like', $searchTerm)
                            ->orWhere('last_name', 'like', $searchTerm);
                    });
            });
        }

        if ($request->subject) {
            $query->where('subject_title', $request->subject);
        }

        if ($request->date_filter) {
            if (str_contains($request->date_filter, 'to')) {
                $dates = explode(" to ", $request->date_filter);
                $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->format('Y-m-d');
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $formatted = Carbon::createFromFormat('d/m/Y', $request->date_filter)->format('Y-m-d');
                $query->whereDate('created_at', $formatted);
            }
        }

        $total = $query->count();

        $data = $query->with('user.employee')
            ->offset($offset)
            ->limit($limit)
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }

    public function delete($id)
    {
        Notes::where('id', $id)->delete();
        return response()->json(array('message' => "Note successfully deleted"));
    }

    public function notesPdf($file)
    {
        $filePath = storage_path('app/public/attachments/notes/' . $file);
        if (!file_exists($filePath)) {
            abort(404, 'The PDF file does not exist.');
        }

        $mimeType = Storage::mimeType($filePath);
        return response()->download(
            $filePath,
            null,
            ['Content-Type' => $mimeType],
            'inline'
        );
    }
}
