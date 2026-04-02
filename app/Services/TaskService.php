<?php

namespace App\Services;

use App\Models\Task;
use App\Notifications\AssignPostNotification;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class TaskService extends Service
{
    private UserRepository $userRepository;

    public function __construct(private readonly TaskRepository $repository)
    {
        parent::__construct();

        $this->userRepository = new UserRepository;
    }

    /**
     * Get Task List
     */
    public function getTasks(array $queryParams): ServiceResult
    {
        try {
            $tasks = $this->repository->getTasksWithFilter($queryParams);
            $this->result->setData($tasks);
        } catch (Exception $exception) {
            $message = 'Failed to fetch Tasks';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Create New Task
     */
    public function createTask(array $data): ServiceResult
    {
        DB::beginTransaction();

        try {
            $data['assign_by'] = Auth::id();
            $data['assign_user_id'] = $data['assign_to']['id'];

            $task = $this->repository->createTask($data);

            if (! empty($data['topics'])) {
                $topicIds = array_column($data['topics'], 'id');
                $this->repository->attachTopics($task, $topicIds);
            }

            //send notification to assigned user
            $user = $this->userRepository->findById($data['assign_user_id']);
            $user->notify(new AssignPostNotification($task));

            DB::commit();

            $this->result->setData($task);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'Task creation Failed';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Update a task's information.
     */
    public function updateTask(Task $task, array $data): ServiceResult
    {
        DB::beginTransaction();

        try {
            if (! empty($data['assign_to']['id'])) {
                $data['assign_user_id'] = $data['assign_to']['id'];
            }

            $task = $this->repository->updateTask($task, $data);

            if (isset($data['topics'])) {
                $topicIds = array_column($data['topics'], 'id');
                $task = $this->repository->syncTopics($task, $topicIds);
            }

            DB::commit();
            $this->result->setData($task);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'Task update Failed';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Soft Delete a Task
     */
    public function deleteTask(Task $task): ServiceResult
    {
        try {
            $this->repository->deleteTask($task);
            $this->result->setDeleted();
        } catch (Exception $exception) {
            $message = 'Task deletion Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Permanent Delete a Task
     */
    public function permanentDeleteTask(Task $task): ServiceResult
    {
        try {
            $this->repository->permanentDeleteTask($task);
            $this->result->setDeleted();
        } catch (Exception $exception) {
            $message = 'Task deletion Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }
}
