<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TagController extends Controller
{
    public function attach(StoreTagRequest $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $tag = $request->user()->tags()->firstOrCreate([
            'name' => $request->validated('name'),
        ]);

        $task->tags()->syncWithoutDetaching([$tag->id]);

        return redirect()->back();
    }

    public function detach(Request $request, Task $task, Tag $tag): RedirectResponse
    {
        Gate::authorize('update', $task);
        abort_unless($tag->user_id === $request->user()->id, 403);

        $task->tags()->detach($tag->id);

        return redirect()->back();
    }
}
