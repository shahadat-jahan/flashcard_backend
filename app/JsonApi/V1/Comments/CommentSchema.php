<?php

namespace App\JsonApi\V1\Comments;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\SoftDelete;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\OnlyTrashed;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WithTrashed;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\SoftDeletes;

class CommentSchema extends Schema
{
    use SoftDeletes;

    /**
     * The model the schema corresponds to.
     */
    public static string $model = Comment::class;

    /**
     * The schema type.
     */
    public static function type(): string
    {
        return 'comments';
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

    /**
     * Get the resource fields.
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('content'),
            DateTime::make('createdAt', 'created_at')->sortable()->readOnly(),
            DateTime::make('updatedAt', 'update_at')->sortable()->readOnly(),
            SoftDelete::make('deletedAt', 'deleted_at')->sortable()->readOnly(),
            BelongsTo::make('user', 'user')->type('users')->readOnly(),
            BelongsTo::make('post', 'post')->type('posts')->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     */
    public function filters(): array
    {
        $list = [
            WhereIdIn::make($this),
            Where::make('content'),
        ];

        if (Auth::user()->isAdmin()) {
            $list[] = WithTrashed::make('with_trashed');
            $list[] = OnlyTrashed::make('trashed');
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
