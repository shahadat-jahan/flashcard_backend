<?php

namespace App\JsonApi\V1\Tasks;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\Boolean;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsToMany;
use LaravelJsonApi\Eloquent\Fields\SoftDelete;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\OnlyTrashed;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WithTrashed;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class TaskSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     */
    public static string $model = Task::class;

    /**
     * The schema type.
     */
    public static function type(): string
    {
        return 'tasks';
    }

    /**
     * Whether resources of this type have a self link.
     */
    protected bool $selfLink = true;

    /**
     * Resources default sort.
     *
     * @var array|string
     */
    protected $defaultSort = ['-createdAt', '-id'];

    protected ?array $defaultPagination = ['limit' => 10];

    /**
     * Get the resource fields.
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('subject')->sortable(),
            Str::make('note'),
            Number::make('status')->sortable(),
            Boolean::make('notify_before_deadline'),
            DateTime::make('due_date')->sortable(),
            DateTime::make('published_at')->sortable(),
            DateTime::make('createdAt', 'created_at')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
            SoftDelete::make('deletedAt'),
            BelongsTo::make('assign_to', 'user')->type('users')->readOnly(),
            BelongsTo::make('assign_by', 'assignBy')->type('users')->readOnly(),
            BelongsToMany::make('topics')->type('topics')->readOnly(),
            BelongsTo::make('published_by', 'publishedBy')->type('users')->readOnly(),
            BelongsTo::make('post', 'post')->type('posts')->readOnly(),
            BelongsTo::make('post.topics', 'post.topics')->type('topics')->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     */
    public function filters(): array
    {
        $list = [
            WhereIdIn::make($this),
            Where::make('subject'),
            Where::make('note'),
            Where::make('created_date_form'),
            Where::make('created_date_to'),
            Where::make('due_date_form'),
            Where::make('due_date_to'),
        ];

        if (Auth::user()->isAdmin()) {
            $list[] = Where::make('assign_user_id');
            $list[] = WithTrashed::make('with_trashed');
            $list[] = OnlyTrashed::make('trashed');
            $list[] = Where::make('status');
        }

        return $list;
    }

    /**
     * Get the resource paginator.
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make()->withoutNestedMeta();
    }
}
