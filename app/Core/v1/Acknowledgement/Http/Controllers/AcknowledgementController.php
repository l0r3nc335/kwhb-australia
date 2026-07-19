<?php

namespace App\Core\v1\Acknowledgement\Http\Controllers;

use App\Core\v1\Acknowledgement\Models\Acknowledgement;
use App\Core\v1\Acknowledgement\Repositories\AcknowledgementRepository;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class AcknowledgementController extends AppController
{
    protected $repository;

    public function __construct()
    {
        $this->repository = new AcknowledgementRepository;
        parent::__construct();
    }

    // List all acknowledgements, optionally filter by resource
    public function index(Request $request)
    {
        $resourceId = $request->get('resource_id');
        $query = Acknowledgement::query();
        if ($resourceId) {
            $query->where('resource_id', $resourceId);
        }
        $data = $query->get();
        return response()->json($data);
    }

    // Show a single acknowledgement
    public function show($id)
    {
        $ack = Acknowledgement::with('resource')->findOrFail($id);
        return response()->json($ack);
    }

    // Store new acknowledgement
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'is_acknowledged' => 'boolean',
        ]);
        $ack = $this->repository->store(
            $validated['resource_id'],
            $validated['is_acknowledged'] ?? false
        );
        return response()->json($ack, 201);
    }

    // Update acknowledgement
    public function update(Request $request, $id)
    {
        $ack = Acknowledgement::findOrFail($id);
        $validated = $request->validate([
            'is_acknowledged' => 'boolean',
        ]);
        $ack = $this->repository->update($ack, $validated['is_acknowledged'] ?? false);
        return response()->json($ack);
    }

    // Delete acknowledgement
    public function delete($id)
    {
        $ack = Acknowledgement::findOrFail($id);
        $ack->delete();
        return response()->json(['message' => 'Acknowledgement deleted!'], 201);
    }
}
