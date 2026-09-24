<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tasks = $request->user()
            ->tasks()
            ->latest()
            ->paginate(10);

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        $task = $request->user()->tasks()->create($request->validated());

        return new TaskResource($task);
    }

    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        Gate::authorize('update', $task);

        $task->update($request->validated());

        return new TaskResource($task);
    }

    public function destroy(Task $task): Response
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }

    public function complete(Task $task): TaskResource
    {
        Gate::authorize('update', $task);

        $task->update(['is_completed' => ! $task->is_completed]);

        return new TaskResource($task);
    }
}
