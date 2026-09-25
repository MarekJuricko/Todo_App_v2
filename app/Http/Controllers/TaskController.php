<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(IndexTaskRequest $request): Response
    {
        $tasks = $request->user()
            ->tasks()
            ->with('tags')
            ->when($request->filled('search'), fn($q) => $q->search($request->string('search')->toString()))
            ->when($request->query('status') === 'completed', fn($q) => $q->completed())
            ->when($request->query('status') === 'pending', fn($q) => $q->pending())
            ->when($request->filled('tag'), fn($q) => $q->withTag($request->integer('tag')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'tags' => $request->user()->tags()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'status', 'tag']),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $request->user()->tasks()->create($request->validated());

        return redirect()->back();
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $task->update($request->validated());

        return redirect()->back();
    }

    public function destroy(Task $task): RedirectResponse
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return redirect()->back();
    }

    public function complete(Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $task->update(['is_completed' => ! $task->is_completed]);

        return redirect()->back();
    }
}
