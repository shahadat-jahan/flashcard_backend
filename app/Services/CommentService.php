<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\UserMentionedNotification;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class CommentService extends Service
{
    private readonly PostRepository $postRepository;

    public function __construct(private readonly CommentRepository $repository)
    {
        parent::__construct();
        $this->postRepository = new PostRepository;
    }

    public function getComments(Request $request): ServiceResult
    {
        try {
            $comments = $this->repository->getCommentsWithFilter($request->get('filter'));

            $this->result->setData($comments);
        } catch (Exception $exception) {
            $message = 'Failed to fetch comments';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function createComment(array $data): ServiceResult
    {
        DB::beginTransaction();
        try {
            $data['post_id'] = $data['post']['id'];
            $comment = $this->repository->createComment($data);

            // Extract mentioned userId from content
            $mentionedUserIds = $this->getMentionedUserId($data['content']);

            if ($mentionedUserIds) {
                // Fetch the post and mentioned users
                $post = $this->postRepository->findById($data['post_id']);
                $mentionedUsers = User::whereIn('id', $mentionedUserIds)->get();

                // Filter users to only include the post author and admins
                $validMentionedUsers = $mentionedUsers->filter(function ($user) use ($post) {
                    return $user->id === $post->author->id || $user->isAdmin();
                });

                if ($validMentionedUsers->isNotEmpty()) {
                    // Attach valid mentions to the comment
                    $comment->mentions()->sync($validMentionedUsers->pluck('id'));

                    // Notify only valid mentioned users
                    foreach ($validMentionedUsers as $mentionedUser) {
                        $mentionedUser->notify(new UserMentionedNotification($comment));
                    }
                } else {
                    // Log a message indicating no valid mentions
                    Log::info('No valid users for mentions on comment', [
                        'comment_id' => $comment->id,
                        'post_id' => $data['post_id'],
                        'mentionedUserIds' => $mentionedUserIds,
                    ]);
                }
            }
            DB::commit();

            $this->result->setData($comment);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'Comment creation failed';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function updateComment(Comment $comment, array $data): ServiceResult
    {
        DB::beginTransaction();
        try {
            $data['post_id'] = $data['post']['id'];
            $comment = $this->repository->updateComment($comment, $data);

            // Extract mentioned userId from updated content
            $newMentionedUserIds = $this->getMentionedUserId($data['content']);

            if ($newMentionedUserIds) {
                // Get existing mentioned user IDs to avoid redundant notifications
                $existingMentionedUserIds = $comment->mentions->pluck('id')->toArray();

                // Find new mentioned users by usernames
                $newMentionedUsers = User::whereIn('id', $newMentionedUserIds)->get();

                // Filter only valid users (post author or admin)
                $post = $this->postRepository->findById($data['post_id']);
                $validNewMentionedUsers = $newMentionedUsers->filter(function ($user) use ($post) {
                    return $user->id === $post->author->id || $user->isAdmin();
                });

                // Get IDs of valid users that weren't already mentioned
                $validNewMentionedUserIds = $validNewMentionedUsers->pluck('id')->toArray();
                $newlyMentionedUserIds = array_diff($validNewMentionedUserIds, $existingMentionedUserIds);

                if ($newlyMentionedUserIds) {
                    // Sync mentions with valid new mentioned user IDs
                    $comment->mentions()->sync($validNewMentionedUserIds);

                    // Notify only valid new mentioned users
                    foreach ($validNewMentionedUsers->whereIn('id', $newlyMentionedUserIds) as $newMentionedUser) {
                        $newMentionedUser->notify(new UserMentionedNotification($comment));
                    }
                } else {
                    // Log a message indicating no valid mentions
                    Log::info('No valid users for mentions on comment', [
                        'comment_id' => $comment->id,
                        'post_id' => $data['post_id'],
                        'mentionedUserIds' => $newMentionedUserIds,
                    ]);
                }
            }
            DB::commit();

            $this->result->setData($comment);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'Comment update failed';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function deleteComment(Comment $comment): ServiceResult
    {
        try {
            $this->repository->deleteComment($comment);
            $this->result->setDeleted();
        } catch (Exception $exception) {
            $message = 'Comment deletion Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    private function getMentionedUserId($content): array
    {
        preg_match_all('/@(\w+)/', $content, $matches);

        return $matches[1];
    }
}
