<?php

namespace App\JsonApi\V1\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsToMany;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\OnlyTrashed;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WithTrashed;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\SoftDeletes;

class UserSchema extends Schema
{
    use SoftDeletes;

    /**
     * The model the schema corresponds to.
     */
    public static string $model = User::class;

    /**
     * The maximum include path depth.
     */
    protected int $maxDepth = 3;

    /**
     * The schema type.
     */
    public static function type(): string
    {
        return 'users';
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
            Str::make('first_name')->sortable(),
            Str::make('last_name')->sortable(),
            Str::make('email')->sortable(),
            Str::make('password')->hidden(),
            Str::make('password_confirmation')->hidden(),
            Str::make('avatar'),
            Number::make('status')->sortable(),
            Number::make('role')->sortable(),
            DateTime::make('email_verified_at')->readOnly(),
            DateTime::make('createdAt')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
            BelongsToMany::make('topics')->type('topics')->readOnly(),
            BelongsTo::make('designation', 'designation')->type('designations')->readOnly(),
            BelongsToMany::make('followers', 'followers')->type('users')->readOnly(),
            BelongsToMany::make('following', 'followings')->type('users')->readOnly(),
            BelongsToMany::make('likedPosts', 'likedPosts')->type('posts')->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     */
    public function filters(): array
    {
        $list = [
            WhereIdIn::make($this),
            Where::make('name'),
            Where::make('first_name'),
            Where::make('last_name'),
            Where::make('email'),
            Where::make('designation'),
        ];

        if (Auth::user()->isAdmin()) {
            $list[] = Where::make('created_date_form');
            $list[] = Where::make('created_date_to');
            $list[] = Where::make('status');
            $list[] = Where::make('role');
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
