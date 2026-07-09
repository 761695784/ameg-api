<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectStudyRequestRequest;
use App\Models\ProjectStudyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectStudyRequestController extends Controller
{
    /**
     * POST /api/project-study-requests
     */
    public function store(StoreProjectStudyRequestRequest $request)
    {
        $validated = $request->validated();

        $projectRequest = DB::transaction(function () use ($validated, $request) {
            $projectRequest = ProjectStudyRequest::create([
                'name' => $validated['name'],
                'company' => $validated['company'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'city' => $validated['city'] ?? null,
                'establishment_type' => $validated['establishment_type'] ?? null,
                'description' => $validated['description'],
                'estimated_budget' => $validated['estimated_budget'] ?? null,
                'desired_deadline' => $validated['desired_deadline'] ?? null,
            ]);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('project-study-documents', 'public');

                    $projectRequest->documents()->create([
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                    ]);
                }
            }

            return $projectRequest;
        });

        // TODO: notifier l'admin par email

        return response()->json($projectRequest->load('documents'), 201);
    }

    /**
     * GET /api/admin/project-study-requests
     */
    public function index()
    {
        return response()->json(
            ProjectStudyRequest::with('documents')->latest()->paginate(20)
        );
    }

    public function show(ProjectStudyRequest $projectStudyRequest)
    {
        return response()->json($projectStudyRequest->load('documents'));
    }

    public function update(Request $request, ProjectStudyRequest $projectStudyRequest)
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:nouveau,en_cours,traite'],
            'admin_reply' => ['nullable', 'string'],
        ]);

        if (isset($data['admin_reply'])) {
            $data['replied_at'] = now();
        }

        $projectStudyRequest->update($data);

        return response()->json($projectStudyRequest);
    }

    public function destroy(ProjectStudyRequest $projectStudyRequest)
    {
        $projectStudyRequest->delete();

        return response()->json(null, 204);
    }
}
