<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Designation;
use App\Models\Post;
use App\Models\Task;
use App\Models\Topic;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\DesignationPolicy;
use App\Policies\PostPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TopicPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Topic::class, TopicPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Designation::class, DesignationPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
    }
}
