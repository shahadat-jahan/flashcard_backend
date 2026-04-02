<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Post;
use App\Models\Task;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Define a seeding flag
        define('IS_SEEDING', true);

        $designations = Designation::factory(5)->create();

        $topics = Topic::factory(20)->create();

        $users = User::factory(10)->create();

        // Assign designation to users
        $users->each(function ($user) use ($designations) {
            $user->update([
                'designation_id' => $designations->random()->id,
            ]);
        });

        // Assign topics to users
        $users->each(function ($user) use ($topics) {
            $user->topics()->attach(
                $topics->random(3)->pluck('id')->toArray()
            );
        });

        $tasks = Task::factory(15)->create();

        $tasks->each(function ($task) use ($users) {
            $task->update([
                'assign_user_id' => $users->random()->id,
                'assign_by' => 1,
            ]);
        });

        $posts = Post::factory(20)->create();

        // Assign topics to posts
        $posts->each(function ($post) use ($topics) {
            $post->topics()->attach(
                $topics->random(3)->pluck('id')->toArray()
            );
        });
    }
}
