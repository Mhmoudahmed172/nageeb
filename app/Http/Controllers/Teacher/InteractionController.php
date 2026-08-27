<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreCommentReplyRequest;
use App\Models\Comment;
use App\Notifications\CommentRepliedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InteractionController extends Controller
{
    public function index(Request $request): View
    {
        $questions = Comment::query()
            ->with(['user', 'lesson.unit.semester.course', 'replies.user'])
            ->whereNull('parent_id')
            ->whereHas(
                'lesson.unit.semester.course',
                fn ($query) => $query->where('teacher_id', auth()->id()),
            )
            ->latest()
            ->get();

        return view('teacher.interactions.index', [
            'questions' => $questions,
            'focusQuestionId' => $request->integer('question') ?: null,
        ]);
    }

    public function reply(StoreCommentReplyRequest $request, Comment $comment): RedirectResponse
    {
        abort_unless($comment->parent_id === null, 422);
        abort_unless($comment->lesson->unit->semester->course->teacher_id === auth()->id(), 403);

        $reply = Comment::query()->create([
            'lesson_id' => $comment->lesson_id,
            'user_id' => auth()->id(),
            'message' => $request->validated('message'),
            'parent_id' => $comment->id,
        ]);

        $reply->load('lesson.unit');
        $comment->user->notify(new CommentRepliedNotification($reply));

        return redirect()
            ->route('teacher.interactions.index')
            ->with('status', 'تم إرسال الرد.');
    }
}
