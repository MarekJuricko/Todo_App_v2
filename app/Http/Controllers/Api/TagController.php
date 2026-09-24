<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Resources\TagResource;
use App\Http\Resources\TaskResource;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TagController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return TagResource::collection(
            $request->user()->tags()->orderBy('name')->get()
        );
    }

    public function attach(StoreTagRequest $request, Task $task): TaskResource
    {
        Gate::authorize('update', $task);

        $tag = $request->user()->tags()->firstOrCreate([
            'name' => $request->validated('name'),
        ]);

        $task->tags()->syncWithoutDetaching([$tag->id]);

        return new TaskResource($task->load('tags'));
    }

    public function detach(Request $request, Task $task, Tag $tag): TaskResource
    {
        Gate::authorize('update', $task);
        abort_unless($tag->user_id === $request->user()->id, 403);

        $task->tags()->detach($tag->id);

        return new TaskResource($task->load('tags'));
    }
}
