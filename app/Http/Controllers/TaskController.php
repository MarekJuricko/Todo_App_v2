<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTaskRequest;
use Illuminate\Http\Request;
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
}
