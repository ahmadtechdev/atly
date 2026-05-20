<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        $this->authorize('create', [TaskComment::class, $task]);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => trim($data['body']),
        ]);

        $comment->load('user');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'comment' => $this->payload($comment),
            ]);
        }

        return redirect()->route('tasks.show', $task);
    }

    public function destroy(Request $request, Task $task, TaskComment $comment): JsonResponse|RedirectResponse
    {
        abort_unless($comment->task_id === $task->id, 404);
        $this->authorize('delete', $comment);

        $comment->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Comment deleted.']);
        }

        return redirect()->route('tasks.show', $task);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(TaskComment $comment): array
    {
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'created_at' => $comment->created_at?->toIso8601String(),
            'created_at_label' => $comment->created_at?->diffForHumans(),
            'delete_url' => route('tasks.comments.destroy', [$comment->task_id, $comment->id]),
            'author' => [
                'id' => $comment->user?->id,
                'name' => $comment->user?->name,
                'initials' => $comment->user?->initials(),
                'avatar_url' => $comment->user?->avatar_url,
            ],
        ];
    }
}
