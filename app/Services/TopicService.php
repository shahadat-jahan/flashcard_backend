<?php

namespace App\Services;

use App\Models\Topic;
use App\Repositories\TopicRepository;
use Illuminate\Http\Request;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class TopicService extends Service
{
    public function __construct(private readonly TopicRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Get topic list
     */
    public function getTopics(Request $request): ServiceResult
    {
        try {
            $topics = $this->repository->getTopicsWithFilter($request->get('filter'));
            $this->result->setData($topics);
        } catch (Exception $exception) {
            $message = 'Failed to fetch topics';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Create New Topic
     */
    public function createTopic(array $data): ServiceResult
    {
        try {
            $topic = $this->repository->createTopic($data);
            $this->result->setData($topic);
        } catch (Exception $exception) {
            $message = 'Topics creation Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Update Topic info
     */
    public function updateTopic(Topic $topic, array $data): ServiceResult
    {
        try {
            $topic = $this->repository->updateTopic($topic, $data);
            $this->result->setData($topic);
        } catch (Exception $exception) {
            $message = 'Topics update Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Soft delete a Topic
     */
    public function deleteTopic(Topic $topic): ServiceResult
    {
        try {
            $this->repository->deleteTopic($topic);
            $this->result->setDeleted();
        } catch (Exception $exception) {
            $message = 'Topics deletion Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Get a Topic's posts.
     */
    public function getTopicPosts(Topic $topic): ServiceResult
    {
        try {
            $posts = $this->repository->getTopicPostsWithPagination($topic, 10);
            $this->result->setData($posts);
        } catch (Exception $exception) {
            $message = "Topic's post fetching failed";

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }
}
