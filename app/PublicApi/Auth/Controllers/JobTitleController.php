<?php

namespace App\PublicApi\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Core\v1\JobTitle\Models\JobTitle;
use Illuminate\Http\Request;

class JobTitleController extends Controller
{
    public function store(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:job_titles,name',
            'description' => 'nullable|string|max:500',
        ]);

        // Create the job title
        $jobTitle = JobTitle::create($validated);

        return response()->json([
            'message' => 'Job title created successfully',
            'data'    => $jobTitle,
        ], 201);
    }
}
