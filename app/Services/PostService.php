<?php

namespace App\Services;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\ServiceResultType as ResultType;
use App\Enums\TaskStatus;
use App\Library\FileManagerLibrary;
use App\Models\Post;
use App\Notifications\ApprovedPostNotification;
use App\Notifications\AssignPostNotification;
use App\Notifications\DeclinedPostNotification;
use App\Notifications\PostLikeNotification;
use App\Notifications\SubmittedPostNotification;
use App\Repositories\PostRepository;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class PostService extends Service
{
    private TaskRepository $taskRepository;

    private UserRepository $userRepository;

    private Filesystem $publicStorage;

    public function __construct(private readonly PostRepository $repository, private readonly FileManagerLibrary $fileManagerLibrary)
    {
        parent::__construct();

        $this->userRepository = new UserRepository;
        $this->taskRepository = new TaskRepository;
        $this->publicStorage = Storage::disk('public');
    }

    /**
     * Get Post List
     */
    public function getPosts(array $queryParams): ServiceResult
    {
        try {
            $posts = $this->repository->getPostsWithFilter($queryParams);
            $this->result->setData($posts);
        } catch (Exception $exception) {
            $message = 'Failed to fetch posts';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Create New Post
     */
    public function createPost(array $data): ServiceResult
    {
        DB::beginTransaction();
        try {
            $data['assign_by'] = Auth::id();
            $data['status'] = PostStatus::from($data['action_type']);
            $data['type'] = PostType::from($data['post_type']);

            if ($data['status'] === PostStatus::APPROVED) {
                $data['approved_by'] = Auth::id();
                $data['approved_at'] = now();
                $data['submitted_at'] = now();
                $taskStatus = TaskStatus::APPROVED;
            } elseif ($data['status'] === PostStatus::PENDING) {
                $data['submitted_at'] = now();
                $taskStatus = TaskStatus::SUBMITTED;
            } else {
                $taskStatus = TaskStatus::PENDING;
            }

            if (isset($data['unused_image_urls'])) {
                foreach ($data['unused_image_urls'] as $unused_image_url) {
                    // Remove the base URL part to get the relative path
                    $relativePath = str_replace(config('filesystems.disks.public.url').'/', '', $unused_image_url);

                    if ($relativePath && $this->publicStorage->exists($relativePath)) {
                        $this->publicStorage->delete($relativePath);
                    }
                }
            }
            $taskData = [
                'assign_user_id' => $data['author']['id'],
                'subject' => $data['title'],
                'note' => $data['note'],
                'status' => $taskStatus,
                'notify_before_deadline' => false,
                'due_date' => $data['due_date'] ?: Carbon::now()->addWeek(),
                'assign_by' => $data['assign_by'],
                'submitted_at' => $data['submitted_at'] ?? null,
            ];
            $task = $this->taskRepository->createTask($taskData);
            $data['task_id'] = $task->id;
            $post = $this->repository->createPost($data);

            $this->sendNotification([$post->id], $data['status']);

            if (! empty($data['topics'])) {
                $topicIds = array_column($data['topics'], 'id');
                $this->repository->attachTopics($post, $topicIds);
            }

            DB::commit();

            $this->result->setData($post);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'Post creation Failed';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Update a post's information.
     */
    public function updatePost(Post $post, array $data): ServiceResult
    {
        DB::beginTransaction();

        try {
            $data['status'] = PostStatus::from($data['action_type']);
            $data['type'] = PostType::from($data['post_type']);

            if ($data['status'] === PostStatus::APPROVED) {
                $data['approved_by'] = Auth::id();
                $data['approved_at'] = now();
            }

            if (isset($data['unused_image_urls'])) {
                foreach ($data['unused_image_urls'] as $unused_image_url) {
                    // Remove the base URL part to get the relative path
                    $relativePath = str_replace(config('filesystems.disks.public.url').'/', '', $unused_image_url);

                    if ($relativePath && $this->publicStorage->exists($relativePath)) {
                        $this->publicStorage->delete($relativePath);
                    }
                }
            }

            $post = $this->repository->updatePost($post, $data);

            $this->sendNotification([$post->id], $data['status']);

            if (isset($data['topics'])) {
                $topicIds = array_column($data['topics'], 'id');
                $post = $this->repository->syncTopics($post, $topicIds);
            }

            // if task exists in post
            if ($post->task) {
                $statusMapping = [
                    PostStatus::DRAFT->value => [
                        'status' => TaskStatus::PENDING,
                        'due_date' => $data['due_date'] ?? $post->task->due_date,
                        'note' => $data['note'],
                    ],
                    PostStatus::PENDING->value => [
                        'status' => TaskStatus::SUBMITTED,
                        'submitted_at' => now(),
                        'due_date' => $data['due_date'] ?? $post->task->due_date,
                        'note' => $data['note'],
                    ],
                    PostStatus::APPROVED->value => [
                        'status' => TaskStatus::APPROVED,
                        'due_date' => $data['due_date'] ?? $post->task->due_date,
                        'note' => $data['note'],
                    ],
                    PostStatus::DECLINED->value => [
                        'status' => TaskStatus::DECLINED,
                        'due_date' => $data['due_date'] ?? $post->task->due_date,
                        'note' => $data['note'],
                    ],
                ];
                // Check if the post status is in the mapping
                if (array_key_exists($post->status->value, $statusMapping)) {
                    $this->taskRepository->updateTask($post->task, $statusMapping[$post->status->value]);
                }
            }

            DB::commit();

            $this->result->setData($post);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'Post update Failed';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Update a post's information.
     */
    public function updatePostStatus(Post $post, array $data): ServiceResult
    {
        DB::beginTransaction();

        try {
            $status = PostStatus::from($data['status']);

            if ($status === PostStatus::APPROVED) {
                $post = $this->repository->approvePost($post);
            } elseif ($status === PostStatus::DECLINED) {
                $post = $this->repository->declinePost($post, $data['decline_reason'] ?? null);
            } else {
                $post = $this->repository->updatePost($post, ['status' => $status]);
            }

            $statusMapping = $this->getStatusMapping();
            if ($post->task && array_key_exists($post->status->value, $statusMapping)) {
                $this->taskRepository->updateTask($post->task, $statusMapping[$post->status->value]);
            }

            $this->sendNotification([$post->id], $status);

            DB::commit();

            $this->result->setData($post);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'Post status update Failed';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Bulk Update a post's information.
     */
    public function bulkUpdatePostStatus(array $data): ServiceResult
    {
        DB::beginTransaction();
        try {
            $postIds = $data['post_ids'];
            $status = PostStatus::from($data['status']);

            $updateData = ['status' => $status];
            if ($status === PostStatus::APPROVED) {
                $updateData['approved_by'] = Auth::id();
                $updateData['approved_at'] = now();
            }

            if (! empty($postIds)) {
                $statusMapping = $this->getStatusMapping();

                // Check if the post status is in the mapping
                if (array_key_exists($status->value, $statusMapping)) {
                    $this->taskRepository->bulkUpdateTaskByPostIds($postIds, $statusMapping[$status->value]);
                }

                $this->repository->bulkUpdatePost($postIds, $updateData, $data['decline_reason'] ?? null);
                $this->sendNotification($postIds, $status);
            }

            DB::commit();

            $this->result->setData(['message' => 'Post status updated successfully.'], ResultType::META);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'Post update Failed';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Soft Delete a Post
     */
    public function deletePost(Post $post): ServiceResult
    {
        try {
            $this->repository->deletePost($post);
            $this->result->setDeleted();
        } catch (Exception $exception) {
            $message = 'Post deletion Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Permanent Delete a Post
     */
    public function permanentDeletePost(Post $post): ServiceResult
    {
        try {
            $this->repository->permanentDeletePost($post);
            $this->result->setDeleted();
        } catch (Exception $exception) {
            $message = 'Post deletion Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function postImage(array $data): ServiceResult
    {
        try {
            $path = $this->fileManagerLibrary->postImageUpload($data['image']);
            $response = [
                'jsonapi' => [
                    'version' => '1.0',
                ],
                'meta' => [
                    'success' => ['message' => 'Post image upload successful.'],
                ],
                'data' => [
                    'type' => 'posts',
                    'attributes' => [
                        'image' => config('filesystems.disks.public.url').'/'.$path,
                    ],
                ],
            ];

            $this->result->setData($response, ResultType::JSON);
        } catch (Exception $exception) {
            $message = 'Post image upload Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function postImageDelete(array $path): ServiceResult
    {
        try {
            $response = $this->fileManagerLibrary->postImageDelete($path['image_url']);

            $this->result->setData($response, ResultType::META);
        } catch (Exception $exception) {
            $message = 'Image delete Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function getCommentsByPost(Post $post): ServiceResult
    {
        $comment = $this->repository->getCommentsByPost($post);
        $this->result->setData($comment);

        return $this->result;
    }

    public function likePost(Post $post): ServiceResult
    {
        $authUser = auth()->user();
        $like = $this->repository->attachLikeToPost($authUser, $post);

        if ($like['status']) {
            $post->author->notify(new PostLikeNotification($authUser, $post));
        }

        $this->result->setData($like, ResultType::JSON);

        return $this->result;
    }

    public function unlikePost(Post $post): ServiceResult
    {
        $authUser = auth()->user();
        $like = $this->repository->detachLikeFromPost($authUser, $post);

        $this->result->setData($like, ResultType::JSON);

        return $this->result;
    }

    private function sendNotification(array $postIds, PostStatus $status): void
    {
        // Define the mapping of status to notification classes
        $notifications = [
            PostStatus::APPROVED->value => ApprovedPostNotification::class,
            PostStatus::DECLINED->value => DeclinedPostNotification::class,
            PostStatus::DRAFT->value => AssignPostNotification::class,
        ];

        foreach ($postIds as $postId) {
            $post = $this->repository->findById($postId);
            $author = $post->author;
            $createdBy = $post->createdBy;

            // Notify admin users when status is PENDING
            if ($status === PostStatus::PENDING) {
                $admins = $this->userRepository->getAdminUsers();
                foreach ($admins as $admin) {
                    $admin->notify(new SubmittedPostNotification($post));
                }
            } elseif ($author !== $createdBy) {
                // Check if the status has a mapped notification and send it
                if (isset($notifications[$status->value])) {
                    $notificationClass = $notifications[$status->value];
                    $author->notify(new $notificationClass($post));
                }
            }
        }
    }

    private function getStatusMapping(): array
    {
        return [
            PostStatus::DRAFT->value => ['status' => TaskStatus::PENDING],
            PostStatus::PENDING->value => ['status' => TaskStatus::SUBMITTED, 'submitted_at' => now()],
            PostStatus::APPROVED->value => ['status' => TaskStatus::APPROVED],
            PostStatus::DECLINED->value => ['status' => TaskStatus::DECLINED],
        ];
    }
}
