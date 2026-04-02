<?php

namespace App\Repositories;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TaskRepository extends Repository
{
    /**
     * Get all Tasks with optional filters and pagination.
     */
    public function getTasksWithFilter(array $queryParams = []): LengthAwarePaginator
    {
        $query = Task::query();

        if (Auth::user()->isUser()) {
            $query->where('assign_user_id', Auth::id());
        }

        // Apply Filters
        $filters = $queryParams['filter'] ?? [];

        if (! empty($filters['subject'])) {
            $query->where('subject', 'like', '%'.$filters['subject'].'%');
        }

        if (! empty($filters['note'])) {
            $query->where('note', 'like', '%'.$filters['note'].'%');
        }

        if (! empty($filters['assign_user_id'])) {
            $query->where('assign_user_id', $filters['assign_user_id']);
        }

        if (! empty($filters['created_date_form'])) {
            $query->whereDate('created_at', '>=', $filters['created_date_form']);
        }

        if (! empty($filters['created_date_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_date_to']);
        }

        if (! empty($filters['due_date_form'])) {
            $query->whereDate('due_date', '>=', $filters['due_date_form']);
        }

        if (! empty($filters['due_date_to'])) {
            $query->whereDate('due_date', '<=', $filters['due_date_to']);
        }

        if (isset($filters['status'])) {
            $status = TaskStatus::tryFrom($filters['status']);
            if ($status) {
                $query->where('status', $status);
            }
        }

        // Manage pagination with page number and page size
        $page = $queryParams['page'] ?? [];
        $pageNumber = $page['number'] ?? 1;
        $pageSize = $page['size'] ?? $this->_limit;

        return $query->orderByDesc('id')->paginate(perPage: $pageSize, page: $pageNumber);
    }

    /**
     * Get a Task.
     */
    public function findById(int $id): Task
    {
        return Task::findOrFail($id);
    }

    /**
     * Create a new Task.
     */
    public function createTask(array $data): Task
    {
        $task = Task::create($data);

        return $task->refresh();
    }

    /**
     * Update a Task's information.
     */
    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }

    /**
     * Bulk Update Tasks By postIds.
     */
    public function bulkUpdateTaskByPostIds(array $postIds, array $data): bool
    {
        return Task::whereHas('post', function ($query) use ($postIds) {
            $query->whereIn('id', $postIds);
        })->update($data);
    }

    /**
     * Attach topics to a Task.
     */
    public function attachTopics(Task $task, array $topicIds): Task
    {
        $task->topics()->attach($topicIds);

        return $task->refresh();
    }

    /**
     * Detach Task's topics.
     */
    public function detachTopics(Task $task, array $topicIds): Task
    {
        $task->topics()->detach($topicIds);

        return $task->refresh();
    }

    /**
     * Sync a Task's topics.
     */
    public function syncTopics(Task $task, array $topicIds): Task
    {
        $task->topics()->sync($topicIds);

        return $task->refresh();
    }

    /**
     * Delete a Task.
     */
    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Delete a Task Permanently.
     */
    public function permanentDeleteTask(Task $task): bool
    {
        return $task->forceDelete();
    }
}
