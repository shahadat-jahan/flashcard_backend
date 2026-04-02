<?php

namespace App\JsonApi\V1\Posts;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
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
use LaravelJsonApi\Eloquent\Filters\WhereIn;
use LaravelJsonApi\Eloquent\Filters\WithTrashed;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\SoftDeletes;

class PostSchema extends Schema
{
    use SoftDeletes;

    /**
     * The model the schema corresponds to.
     */
    public static string $model = Post::class;

    /**
     * The maximum include path depth.
     */
    protected int $maxDepth = 3;

    /**
     * The schema type.
     */
    public static function type(): string
    {
        return 'posts';
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
            Str::make('action_type')->hidden(),
            Str::make('post_ids')->hidden(),
            Str::make('decline_reason')->hidden(),
            Str::make('title')->sortable(),
            Str::make('slug'),
            Str::make('post_type')->sortable(),
            Str::make('content'),
            Str::make('note'),
            Str::make('image')->readOnly(),
            Str::make('unused_image_urls')->readOnly(),
            Number::make('status')->sortable(),
            DateTime::make('due_date')->sortable(),
            DateTime::make('approvedAt', 'approved_at')->readOnly(),
            DateTime::make('createdAt', 'created_at')->sortable()->readOnly(),
            DateTime::make('updatedAt', 'updated_at')->sortable()->readOnly(),
            SoftDelete::make('deletedAt', 'deleted_at')->sortable()->readOnly(),
            BelongsTo::make('author', 'author')->type('users')->readOnly(),
            BelongsToMany::make('topics', 'topics')->type('topics')->readOnly(),
            BelongsTo::make('approvedBy', 'approvedBy')->type('users')->readOnly(),
            BelongsTo::make('createdBy', 'createdBy')->type('users')->readOnly(),
            BelongsToMany::make('likes', 'likes')->type('users')->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     */
    public function filters(): array
    {
        $list = [
            WhereIdIn::make($this),
            Where::make('title'),
            Where::make('content'),
            Where::make('created_date_from'),
            Where::make('created_date_to'),
            Where::make('due_date_from'),
            Where::make('due_date_to'),
            Where::make('status'),
            Where::make('author'),
            Where::make('post_type'),
            WhereIn::make('topics'),
        ];

        if (Auth::user()->isAdmin()) {
            $list[] = Where::make('createdBy');
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
