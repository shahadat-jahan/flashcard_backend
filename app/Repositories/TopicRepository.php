<?php

namespace App\Repositories;

use App\Enums\TopicStatus;
use App\Models\Topic;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TopicRepository extends Repository
{
    /**
     * Get all topic with optional filters.
     */
    public function getTopicsWithFilter(?array $filters = null): LengthAwarePaginator
    {
        $query = Topic::query();

        if (Auth::user()->isUser()) {
            $query->where('status', TopicStatus::ACTIVE);
        }

        if (isset($filters['name'])) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($filters['name']).'%']);
        }

        return $query->orderByDesc('id')->paginate($this->_limit);
    }

    /**
     * Find a topic
     */
    public function findById(int $id): Topic
    {
        return Topic::findOrFail($id);
    }

    /**
     * Get topic's posts.
     */
    public function getTopicPostsWithPagination(Topic $topic, ?int $limit = null): LengthAwarePaginator
    {
        return $topic->posts()->orderByDesc('id')->paginate($limit ?? $this->_limit);
    }

    /**
     * Create a new topic.
     */
    public function createTopic(array $data): Topic
    {
        $topic = Topic::create($data);

        return $topic->refresh();
    }

    /**
     * Update a topic's information.
     */
    public function updateTopic(Topic $topic, array $data): Topic
    {
        $topic->update($data);

        return $topic->refresh();
    }

    /**
     * Delete a Topics.
     */
    public function deleteTopic(Topic $topic): bool
    {
        return $topic->delete();
    }
}
