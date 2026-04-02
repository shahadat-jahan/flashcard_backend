<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentRepository extends Repository
{
    public function getCommentsWithFilter(?array $filters = null): LengthAwarePaginator
    {
        $query = Comment::query();

        if (isset($filters['content'])) {
            $query->whereRaw('LOWER(content) LIKE ?', ['%'.strtolower($filters['content']).'%']);
        }
        if (isset($filters['user'])) {
            $query->where('user_id', $filters['user']);
        }
        if (isset($filters['post'])) {
            $query->where('post_id', $filters['post']);
        }

        return $query->orderByDesc('id')->paginate($this->_limit);
    }

    /**
     * Create a new comment.
     */
    public function createComment(array $data): Comment
    {
        return Comment::create($data)->refresh();
    }

    /**
     * Update a comment
     */
    public function updateComment(Comment $comment, array $data): Comment
    {
        $comment->update($data);

        return $comment->refresh();
    }

    /**
     * Delete a comment
     */
    public function deleteComment(Comment $comment): bool
    {
        return $comment->delete();
    }
}
