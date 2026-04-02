<?php

use App\Http\Controllers\V1\CommentController;
use App\Http\Controllers\V1\DashboardController;
use App\Http\Controllers\V1\DesignationController;
use App\Http\Controllers\V1\NotificationController;
use App\Http\Controllers\V1\PostController;
use App\Http\Controllers\V1\ProfileController;
use App\Http\Controllers\V1\TaskController;
use App\Http\Controllers\V1\TopicController;
use App\Http\Controllers\V1\UserController;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;

Route::middleware(['auth:sanctum', 'ensure.json', 'verified'])->group(function () {
    JsonApiRoute::server('v1')->prefix('v1')->resources(function (ResourceRegistrar $server) {
        $server->resource('profile', ProfileController::class)
            ->only('index', 'store')->actions(function ($actions) {
                $actions->post('password', 'updatePassword')->name('password');
                $actions->post('topics', 'attachTopics')->name('topics');
                $actions->withId()->post('follow', 'follow')->name('follow');
                $actions->withId()->delete('unfollow', 'unfollow')->name('unfollow');
            });

        $server->resource('users', UserController::class)->actions(function ($actions) {
            $actions->withId()->post('follow', 'follow')->name('follow');
            $actions->withId()->delete('unfollow', 'unfollow')->name('unfollow');
        });

        $server->resource('topics', TopicController::class)->only('index');

        $server->resource('designations', DesignationController::class)->only('index');

        $server->resource('tasks', TaskController::class)->only('index', 'show');

        $server->resource('posts', PostController::class)->actions(function ($actions) {
            $actions->post('image', 'imageUpload')->name('image.upload');
            $actions->delete('image/delete', 'imageDelete')->name('image.delete');
            $actions->withId()->get('comments', 'comments')->name('comments');
            $actions->withId()->post('like', 'like')->name('like');
            $actions->withId()->delete('unlike', 'unlike')->name('unlike');
        });

        $server->resource('comments', CommentController::class);

        $server->resource('dashboard', DashboardController::class)->only('index');

        $server->resource('notifications', NotificationController::class)->only('index', 'show')->actions(function ($actions) {
            $actions->withId()
                ->get('read', 'read')
                ->name('read');
            $actions->get('read-all', 'readAll')->name('read-all');
        });
    });

    Route::middleware(['admin'])->group(function () {
        JsonApiRoute::server('v1')->prefix('v1')->resources(function (ResourceRegistrar $server) {
            $server->resource('posts', PostController::class)->except('index', 'store', 'update', 'show', 'delete')->actions(function ($actions) {
                $actions->withId()->patch('status', 'updateStatus')->name('status');
                $actions->post('bulk-status-update', 'bulkStatusUpdate')->name('bulk.status.update');
            });

            $server->resource('topics', TopicController::class)->except('index')->actions(function ($actions) {
                $actions->withId()->get('posts', 'posts')->name('posts');
            });

            $server->resource('designations', DesignationController::class)->except('index')->actions(function ($actions) {
                $actions->withId()->get('users', 'users')->name('users');
            });

            $server->resource('tasks', TaskController::class)->only('store', 'update', 'destroy');

            $server->resource('users', UserController::class)->actions(function ($actions) {
                $actions->withId()->patch('password', 'updatePassword')->name('password');
                $actions->withId()->patch('status', 'updateStatus')->name('status');
                $actions->withId()->patch('role', 'updateRole')->name('role');
                $actions->withId()->get('posts', 'posts')->name('posts');
                $actions->withId()->patch('invite', 'sendInvitation')->name('invitation');
            });
        });
    });
});
