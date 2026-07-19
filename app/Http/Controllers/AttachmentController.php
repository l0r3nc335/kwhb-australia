<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\DataResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Auth;

class AttachmentController extends Controller
{

    private $user;
    public function __construct()
    {
        $this->user = Auth::user();
    }
    public function index()
    {
        $tbl = Attachment::all();
        return response()->json($tbl);
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        try {
            $data = Attachment::create($request->all());

            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'store',
                'log_type' => 'Message',
                'action' => 'Create attachment',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $data,
            ]);
            return $this->index();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'store',
                'log_type' => 'Error',
                'action' => 'Create attachment',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }


    public function show(Employee $Employee)
    {
        //
    }

    public function edit(Employee $Employee)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try {
            $cmd  = Attachment::findOrFail($id);
            $logFrom = $cmd->replicate();
            $input = $request->all();

            $cmd->fill($input)->save();
            $logTo = $cmd;
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'update',
                'log_type' => 'Message',
                'action' => 'Update attachment',
                'ip_address' => Helper::instance()->getIp(),
                'data' => "From: " . $logFrom . "\r\nTo: " . $logTo,
            ]);
            return $this->index();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'update',
                'log_type' => 'Error',
                'action' => 'Update attachment',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tbl = Attachment::findOrFail($id);
            Attachment::destroy($id);


            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'destroy',
                'log_type' => 'Message',
                'action' => 'Delete attachment',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $tbl,
            ]);
            return $this->index();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'destroy',
                'log_type' => 'Error',
                'action' => 'Delete attachment',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            return response()->json(['error' => 'There was a problem deleting this Employee.'], 500);
        }
    }
    public function saveAttachment($data)
    {
        $dataResponse = new DataResponse();
        try {

            $attachment =
                json_decode(json_encode($data->attachment), true);

            $exploded = explode(',', $attachment);

            $decoded = base64_decode($exploded[1]);

            if (str_contains($exploded[0], "jpeg"))
                $extension = "jpeg";
            elseif (str_contains($exploded[0], "jpg"))
                $extension = "jpg";
            elseif (str_contains($exploded[0], "png"))
                $extension = "png";
            elseif (str_contains($exploded[0], "gif"))
                $extension = "gif";
            elseif (str_contains($exploded[0], "tiff"))
                $extension = "tiff";
            elseif (str_contains($exploded[0], "bmp"))
                $extension = "bmp";
            elseif (str_contains($exploded[0], "pdf"))
                $extension = "pdf";
            elseif (str_contains($exploded[0], "docx"))
                $extension = "docx";
            elseif (str_contains($exploded[0], "doc"))
                $extension = "doc";
            elseif (str_contains($exploded[0], "pdf"))
                $extension = "pdf";
            else
                $extension = "txt";

            $uuid = Str::uuid();
            $datetime = now();
            $fileName = $datetime->format('YmdHis') . '_' . $uuid . "." . $extension;

            // $directory = public_path() . "/attachments/" . $data->path;
            // if (!is_dir($directory)) {
            //     mkdir($directory, 0777, true);
            // }
            // $path = $directory . "/" . $fileName;

            Storage::disk('public')->putFileAs($data->path, $data->attachment, $fileName);

            //file_put_contents($path, $decoded);
            $attachment = isset($data->id) ? Attachment::find($data->id) : new Attachment();
            $attachment->name = $data->name;
            $attachment->source = $data->source;
            $attachment->source_id = $data->source_id;
            $attachment->file_name = $fileName;
            $attachment->extension_name = $extension;

            $attachment->save();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                //'user_id' => 1,
                'function_name' => 'saveAttachment',
                'log_type' => 'Error',
                'action' => 'Store Attachment',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }
}
