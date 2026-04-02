<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class UserRepository extends Repository
{
    /**
     * Get all users with optional filters and pagination.
     */
    public function getUsersWithFilter(?array $queryParams = null): LengthAwarePaginator
    {
        if (isset($queryParams['search'])) {
            // For elastic search
            $query = User::search(trim($queryParams['search']))
                ->query(function ($q) {
                    $q->join('designations', 'users.designation_id', '=', 'designations.id')
                        ->leftJoin('posts', 'users.id', '=', 'posts.author_id') // Join posts to calculate count
                        ->select(
                            'users.*',
                            'designations.name',
                            DB::raw('COUNT(posts.id) as posts_count') // Count posts for each user
                        )
                        ->groupBy('users.id', 'designations.name')->withCount('following', 'followers', 'likedPosts');
                });
        } else {
            $query = User::query()->withCount('following', 'followers', 'likedPosts');

            // Apply Filters
            $filters = $queryParams['filter'] ?? [];

            if (! empty($filters['name'])) {
                $query->whereRaw('LOWER(first_name) LIKE ?', ['%'.strtolower($filters['name']).'%'])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', ['%'.strtolower($filters['name']).'%']);
            }

            if (! empty($filters['first_name'])) {
                $query->where('first_name', 'like', '%'.$filters['first_name'].'%');
            }

            if (! empty($filters['last_name'])) {
                $query->where('last_name', 'like', '%'.$filters['last_name'].'%');
            }

            if (! empty($filters['email'])) {
                $query->where('email', $filters['email']);
            }

            if (! empty($filters['designation'])) {
                $query->where('designation_id', $filters['designation']);
            }

            if (! empty($filters['status'])) {
                $status = UserStatus::tryFrom($filters['status']);
                if ($status) {
                    $query->where('status', $status);
                }
            }

            if (! empty($filters['role'])) {
                $role = UserRole::tryFrom($filters['role']);
                if ($role) {
                    $query->where('role', $role);
                }
            }

            if (! empty($filters['created_date_form'])) {
                $query->whereDate('created_at', '>=', $filters['created_date_form']);
            }

            if (! empty($filters['created_date_to'])) {
                $query->whereDate('created_at', '<=', $filters['created_date_to']);
            }

            // Apply with count
            $query->withCount('posts')->orderByDesc('id');
        }

        // Manage pagination with page number and page size
        $page = $queryParams['page'] ?? [];
        $pageNumber = $page['number'] ?? 1;
        $pageSize = $page['size'] ?? $this->_limit;

        return $query->paginate(perPage: $pageSize, page: $pageNumber);
    }

    /**
     * Get a user.
     */
    public function findById(int $id): User
    {
        return User::withCount('followers', 'following', 'likedPosts')->findOrFail($id);
    }

    public function getAdminUsers(): \Illuminate\Support\Collection
    {
        return User::where('role', UserRole::ADMIN)->get();
    }

    /**
     * Find user by email, including soft-deleted records.
     */
    public function findUserByEmailWithTrashed(string $email): ?User
    {
        return User::withTrashed()->where('email', $email)->first();
    }

    /**
     * Get user posts.
     */
    public function userPostsWithPagination(User $user, ?int $limit = null): LengthAwarePaginator
    {
        return $user->posts()->orderByDesc('id')->paginate($limit ?? $this->_limit);
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        return User::create($data)->refresh();
    }

    /**
     * Update a user information.
     */
    public function updateUser(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    /**
     * Update a user information.
     */
    public function attachTopics(User $user, array $topicIds): User
    {
        $user->topics()->attach($topicIds);

        return $user->refresh();
    }

    /**
     * Update a post's information.
     */
    public function detachTopics(User $user, array $topicIds): User
    {
        $user->topics()->detach($topicIds);

        return $user->refresh();
    }

    /**
     * Sync a post's information.
     */
    public function syncTopics(User $user, array $topicIds): User
    {
        $user->topics()->sync($topicIds);

        return $user->refresh();
    }

    /**
     * Delete a Post.
     */
    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Restore a deleted user and update password
     */
    public function restoreDeletedUser(User $user, array $data): User
    {
        $user->restore();

        return $this->updateUser($user, $data);
    }

    /**
     * Delete a Post Permanently.
     */
    public function permanentDeleteUser(User $user): bool
    {
        return $user->forceDelete();
    }

    /**
     * Follow user
     */
    public function attachFollowToUser(User $authUser, User $followingUser): array
    {
        $isFollowing = $authUser->following()->where('following_user_id', $followingUser->id)->exists();

        if (! $isFollowing) {
            $authUser->following()->attach($followingUser->id);

            return [
                'message' => 'User followed successfully',
                'status' => true,
            ];
        }

        return [
            'message' => 'You are already followed this user',
            'status' => false,
        ];
    }

    /**
     * Unfollow user
     */
    public function detachFollowFromUser(User $authUser, User $unfollowingUser): array
    {
        $isFollowing = $authUser->following()->where('following_user_id', $unfollowingUser->id)->exists();

        if ($isFollowing) {
            $authUser->following()->detach($unfollowingUser->id);

            return [
                'message' => 'User unfollowed successfully',
                'status' => true,
            ];
        }

        return [
            'message' => 'You have not followed this user yet',
            'status' => false,
        ];
    }

    /**
     * @param User $inviteUser
     * @return array
     */
    public function newInvitation(User $inviteUser): array
    {
        $email = $inviteUser->email;
        $oldTokenRecord = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($oldTokenRecord) {
            Password::broker()->deleteToken($inviteUser);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($inviteUser)
                ->withProperties(['email' => $email, 'oldToken' => $oldTokenRecord->token])
                ->log('Deleted old password reset token.');
        }

        $newToken = Password::broker()->createToken($inviteUser);

        if ($newToken) {
            $inviteUser->notify(new SetPasswordNotification($newToken));

            activity()
                ->causedBy(auth()->user())
                ->performedOn($inviteUser)
                ->withProperties(['email' => $email, 'newToken' => $newToken])
                ->log('Sent a new invitation.');

            return [
                'message' => 'New invitation sent successfully.',
                'status' => true,
            ];
        }

        return [
            'message' => 'New invitation sent unsuccessful.',
            'status' => false,
        ];
    }
}
