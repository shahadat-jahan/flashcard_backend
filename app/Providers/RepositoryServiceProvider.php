<?php

namespace App\Providers;

use App\Library\FileManagerLibrary;
use App\Repositories\CommentRepository;
use App\Repositories\DesignationRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\PostRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TopicRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\CommentService;
use App\Services\DashboardService;
use App\Services\DesignationService;
use App\Services\NotificationService;
use App\Services\PostService;
use App\Services\TaskService;
use App\Services\TopicService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // For User
        $this->app->bind(UserRepository::class);
        $this->app->bind(UserService::class, function ($app) {
            return new UserService($app->make(UserRepository::class), $app->make(FileManagerLibrary::class)
            );
        });

        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService($app->make(UserRepository::class));
        });

        $this->app->bind(DashboardService::class, function ($app) {
            return new DashboardService($app->make(PostRepository::class));
        });

        // For Tasks
        $this->app->bind(TaskRepository::class);
        $this->app->bind(TaskService::class, function ($app) {
            return new TaskService($app->make(TaskRepository::class));
        });

        // For Post
        $this->app->bind(PostRepository::class);
        $this->app->bind(PostService::class, function ($app) {
            return new PostService($app->make(PostRepository::class), $app->make(FileManagerLibrary::class));
        });

        // For Topics
        $this->app->bind(TopicRepository::class);
        $this->app->bind(TopicService::class, function ($app) {
            return new TopicService($app->make(TopicRepository::class));
        });

        // For Designations
        $this->app->bind(DesignationRepository::class);
        $this->app->bind(DesignationService::class, function ($app) {
            return new DesignationService($app->make(DesignationRepository::class));
        });

        // For Notifications
        $this->app->bind(NotificationRepository::class);
        $this->app->bind(NotificationService::class, function ($app) {
            return new NotificationService($app->make(NotificationRepository::class));
        });

        // For Comment
        $this->app->bind(CommentRepository::class);
        $this->app->bind(CommentService::class, function ($app) {
            return new CommentService($app->make(CommentRepository::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
