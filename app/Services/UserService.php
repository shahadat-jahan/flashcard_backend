<?php

namespace App\Services;

use App\Enums\ServiceResultType as ResultType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Library\FileManagerLibrary;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use App\Notifications\UserFollowedNotification;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class UserService extends Service
{
    public function __construct(private readonly UserRepository $repository, private readonly FileManagerLibrary $fileManagerLibrary)
    {
        parent::__construct();
    }

    /**
     * Get User List
     */
    public function getUsers(array $queryParams): ServiceResult
    {
        try {
            $users = $this->repository->getUsersWithFilter($queryParams);
            $this->result->setData($users);
        } catch (Exception $exception) {
            $message = 'Failed to fetch users';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Get single User
     */
    public function getUser(User $user): ServiceResult
    {
        $userData = $this->repository->findById($user->id);

        $this->result->setData($userData);

        return $this->result;
    }

    /**
     * Create new user
     */
    public function createUser(array $data): ServiceResult
    {
        try {
            $data['status'] = UserStatus::INACTIVE;

            if (! empty($data['designation'])) {
                $data['designation_id'] = $data['designation']['id'];
                unset($data['designation']);
            }

            $user = $this->repository->createUser($data);
            $token = Password::broker()->createToken($user);

            $user->notify(new SetPasswordNotification($token));

            $this->result->setData($user);
        } catch (Exception $exception) {
            $message = 'User creation failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Update a user information.
     */
    public function updateUser(User $user, array $data): ServiceResult
    {
        DB::beginTransaction();

        try {
            if (isset($data['avatar'])) {
                $path = $this->fileManagerLibrary->profileImageUpload($user, $data['avatar']);
                $data['avatar'] = $path;
            }
            if (! empty($data['designation'])) {
                $data['designation_id'] = $data['designation']['id'];
                unset($data['designation']);
            }

            $user = $this->repository->updateUser($user, $data);

            if (! empty($data['topics'])) {
                $topicIds = array_column($data['topics'], 'id');
                $user = $this->repository->syncTopics($user, $topicIds);
            }

            DB::commit();

            $this->result->setData($user);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'User update failed';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Update a user password.
     */
    public function updateUserPassword(User $user, array $data): ServiceResult
    {
        try {
            $user = $this->repository->updateUser($user, ['password' => Hash::make($data['password'])]);
            $this->result->setData($user);
        } catch (Exception $exception) {
            $message = 'User password update failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Update a user status.
     */
    public function updateUserStatus(User $user, array $data): ServiceResult
    {
        try {
            $user = $this->repository->updateUser($user, ['status' => UserStatus::from($data['status'])]);
            $this->result->setData($user);
        } catch (Exception $exception) {
            $message = 'User status update failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Update a user role.
     */
    public function updateUserRole(User $user, array $data): ServiceResult
    {
        try {
            $user = $this->repository->updateUser($user, ['role' => UserRole::from($data['role'])]);
            $this->result->setData($user);
        } catch (Exception $exception) {
            $message = 'User role update failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Soft Delete a User
     */
    public function deleteUser(User $user): ServiceResult
    {
        try {
            $this->repository->deleteUser($user);
            $this->result->setDeleted();
        } catch (Exception $exception) {
            $message = 'User deletion failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Permanent Delete a User
     */
    public function forceDelete(User $user): ServiceResult
    {
        try {
            $this->repository->permanentDeleteUser($user);
            $this->result->setDeleted();
        } catch (Exception $exception) {
            $message = 'User deletion failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Get a user posts.
     */
    public function getUserPosts(User $user): ServiceResult
    {
        try {
            $posts = $this->repository->userPostsWithPagination($user, 10);

            $this->result->setData($posts);
        } catch (Exception $exception) {
            $message = "User's post fetching failed";

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function followUser(User $followingUser): ServiceResult
    {
        $authUser = auth()->user();
        $follow = $this->repository->attachFollowToUser($authUser, $followingUser);

        if ($follow['status']) {
            $followingUser->notify(new UserFollowedNotification($authUser));
        }
        $this->result->setData($follow, ResultType::JSON);

        return $this->result;
    }

    public function unfollowUser(User $unfollowingUser): ServiceResult
    {
        $authUser = auth()->user();
        $follow = $this->repository->detachFollowFromUser($authUser, $unfollowingUser);

        $this->result->setData($follow, ResultType::JSON);

        return $this->result;
    }

    public function inviteNewUser(User $inviteUser): ServiceResult
    {
        $invitation = $this->repository->newInvitation($inviteUser);

        $this->result->setData($invitation, ResultType::JSON);

        return $this->result;
    }
}
