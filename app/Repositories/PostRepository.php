<?php

namespace App\Repositories;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Models\PostDeclineReason;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PostRepository extends Repository
{
    /**
     * Get all posts with optional filters and pagination.
     */
    public function getPostsWithFilter(array $queryParams = []): LengthAwarePaginator
    {
        if (isset($queryParams['search'])) {
            $query = Post::search(trim($queryParams['search']))
                ->query(function ($q) {
                    $q->join('users', 'posts.author_id', '=', 'users.id')
                        ->select('posts.*', 'users.first_name', 'users.last_name');

                    if (Auth::user()->isUser()) {
                        $q->whereRaw(
                            '(posts.author_id = ? OR (posts.status = ? AND posts.type IN (?, ?)))',
                            [
                                Auth::id(),
                                PostStatus::APPROVED,
                                PostType::FLASHCARD,
                                PostType::TWEET,
                            ]
                        );
                    }
                });
        } else {
            $query = Post::select('posts.*',
                'tasks.id as task_id',
                'tasks.assign_by',
                'tasks.assign_user_id',
                'tasks.subject',
                'tasks.status as task_status',
                'tasks.due_date',
            )->join('tasks', 'tasks.id', '=', 'posts.task_id');

            if (Auth::user()->isUser()) {
                $query->where(function ($q) {
                    $q->where('author_id', Auth::id())
                        ->orWhere(function ($q) {
                            $q->where('posts.status', PostStatus::APPROVED)
                                ->whereIn('posts.type', [PostType::FLASHCARD, PostType::TWEET]);
                        });
                });
            }

            // Apply Filters
            $filters = $queryParams['filter'] ?? [];

            if (! empty($filters['title'])) {
                $query->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($filters['title']).'%']);
            }

            if (! empty($filters['content'])) {
                $query->whereRaw('LOWER(content) LIKE ?', ['%'.strtolower($filters['content']).'%']);
            }

            if (! empty($filters['topics'])) {
                $topicIds = is_array($filters['topics']) ? $filters['topics'] : [$filters['topics']];
                $query->whereHas('topics', function ($query) use ($topicIds) {
                    $query->whereIn('id', $topicIds);
                });
            }

            if (! empty($filters['due_date_from'])) {
                $query->whereDate('due_date', '>=', $filters['due_date_from']);
            }

            if (! empty($filters['due_date_to'])) {
                $query->whereDate('due_date', '<=', $filters['due_date_to']);
            }

            if (! empty($filters['created_date_from'])) {
                $query->whereDate('created_at', '>=', $filters['created_date_from']);
            }

            if (! empty($filters['created_date_to'])) {
                $query->whereDate('created_at', '<=', $filters['created_date_to']);
            }

            if (isset($filters['status'])) {
                $status = PostStatus::tryFrom($filters['status']);
                if ($status) {
                    $query->where('posts.status', $status);
                }
            }

            if (isset($filters['post_type'])) {
                $type = PostType::tryFrom($filters['post_type']);
                if ($type) {
                    $query->where('posts.type', $type);
                }
            }

            if (Auth::user()->isAdmin()) {
                if (! empty($filters['author'])) {
                    $query->where('author_id', $filters['author']);
                }

                if (! empty($filters['created_by'])) {
                    $query->where('created_by', $filters['createdBy']);
                }
            } elseif (! empty($filters['author'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('posts.type', PostType::FLASHCARD)
                        ->where('author_id', $filters['author']);
                });
            }
            $query->orderByDesc('posts.id');
        }

        // Manage pagination with page number and page size
        $page = $queryParams['page'] ?? [];
        $pageNumber = $page['number'] ?? 1;
        $pageSize = $page['size'] ?? $this->_limit;

        return $query->paginate(perPage: $pageSize, page: $pageNumber);
    }

    /**
     * Get a Post.
     */
    public function findById(int $id): Post
    {
        return Post::findOrFail($id);
    }

    /**
     * Get a Post by TaskId.
     */
    public function getPostByTaskId(int $taskId): Post
    {
        return Post::where('task_id', $taskId)->firstOrFail();
    }

    /**
     * Check If exist post by task Id
     */
    public function isPostExistForTaskId(int $taskId): bool
    {
        return Post::where('task_id', $taskId)->exists();
    }

    /**
     * Create a new Post.
     */
    public function createPost(array $data): Post
    {
        // Create the post and associate it with the task
        $post = Post::create([
            'author_id' => $data['author']['id'],
            'task_id' => $data['task_id'],
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'type' => $data['type'],
            'status' => $data['status'],
            'approved_by' => $data['approved_by'] ?? null,
            'approved_at' => $data['approved_at'] ?? null,
        ]);

        return $post->refresh();
    }

    /**
     * Update a post's information.
     */
    public function updatePost(Post $post, array $data): Post
    {
        $post->update($data);

        return $post->refresh();
    }

    /**
     * Approve a post.
     */
    public function approvePost(Post $post): Post
    {
        $data = [
            'status' => PostStatus::APPROVED,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ];

        $post->update($data);

        return $post->refresh();
    }

    /**
     * Approve a post.
     */
    public function declinePost(Post $post, ?string $reason = null): Post
    {
        $post->update(['status' => PostStatus::DECLINED]);

        // Add Decline Reason
        if ($reason) {
            $this->savePostDeclineReason($post->id, $reason);
        }

        return $post->refresh();
    }

    /**
     * Bulk Update a post's
     */
    public function bulkUpdatePost(array $postIds, array $data, ?string $declineReason = null): bool
    {
        if ($data['status'] === PostStatus::DECLINED && $declineReason) {
            $this->savePostDeclineReason($postIds, $declineReason);
        }

        return Post::whereIn('id', $postIds)->update($data);
    }

    /**
     * Save Post Decline Reason
     */
    public function savePostDeclineReason(array|int $postIds, string $reason): void
    {
        $postIds = is_array($postIds) ? $postIds : [$postIds];

        foreach ($postIds as $id) {
            PostDeclineReason::create([
                'post_id' => $id,
                'reason' => $reason,
                'declined_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Update a post's information.
     */
    public function attachTopics(Post $post, array $topicIds): Post
    {
        $post->topics()->attach($topicIds);

        return $post->refresh();
    }

    /**
     * Update a post's information.
     */
    public function detachTopics(Post $post, array $topicIds): Post
    {
        $post->topics()->detach($topicIds);

        return $post->refresh();
    }

    /**
     * Sync a post's information.
     */
    public function syncTopics(Post $post, array $topicIds): Post
    {
        $post->topics()->sync($topicIds);

        return $post->refresh();
    }

    /**
     * Delete a Post.
     */
    public function deletePost(Post $post): bool
    {
        return $post->delete();
    }

    /**
     * Delete a Post Permanently.
     */
    public function permanentDeletePost(Post $post): bool
    {
        return $post->forceDelete();
    }

    /**
     * status wise post count
     */
    public function statusWisePostCount($user, array $queryParams = []): array
    {
        $query = Post::query();
        if (! $user->isAdmin()) {
            $query->where(function ($query) use ($user) {
                $query->where('author_id', $user->id);
            });
        }

        // Apply Filters
        $filters = $queryParams['filter'] ?? [];

        if (isset($filters['post_type'])) {
            $type = PostType::tryFrom($filters['post_type']);
            if ($type) {
                $query->where('posts.type', $type);
            }
        }
        // Fetch total and status counts in a single query
        $counts = $query->selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as declined',
            [
                PostStatus::DRAFT->value,
                PostStatus::PENDING->value,
                PostStatus::APPROVED->value,
                PostStatus::DECLINED->value,
            ]
        )->first();

        return [
            'draft' => $counts->draft,
            'pending' => $counts->pending,
            'approved' => $counts->approved,
            'declined' => $counts->declined,
            'total' => $counts->total,
        ];
    }

    public function getCommentsByPost(Post $post): Collection
    {
        return $post->comments;
    }

    /**
     * like post by user
     */
    public function attachLikeToPost(User $authUser, Post $post): array
    {
        $isLiked = $post->likes()->where('user_id', $authUser->id)->exists();

        if (! $isLiked) {
            $post->likes()->attach($authUser->id);
            $post->increment('total_like');

            return [
                'message' => 'Post liked successfully',
                'status' => true,
            ];
        }

        return [
            'message' => 'You already liked this post',
            'status' => false,
        ];
    }

    /**
     * unlike post by user
     */
    public function detachLikeFromPost(User $authUser, Post $post): array
    {
        $isLiked = $post->likes()->where('user_id', $authUser->id)->exists();

        if ($isLiked) {
            $post->likes()->detach($authUser->id);
            $post->decrement('total_like');

            return [
                'message' => 'Post Unlike successfully',
                'status' => true,
            ];
        }

        return [
            'message' => 'You have not liked this post yet',
            'status' => false,
        ];
    }
}
