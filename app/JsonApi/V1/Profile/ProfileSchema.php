<?php

namespace App\JsonApi\V1\Profile;

use App\JsonApi\Proxies\Profile;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsToMany;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\ProxySchema;

class ProfileSchema extends ProxySchema
{
    /**
     * The model the schema corresponds to.
     */
    public static string $model = Profile::class;

    /**
     * The schema type.
     */
    public static function type(): string
    {
        return 'profile';
    }

    /**
     * Get the resource fields.
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('first_name'),
            Str::make('last_name'),
            Str::make('email')->readOnly(),
            Str::make('avatar'),
            Str::make('current_password')->readOnly(),
            Str::make('password')->hidden(),
            Str::make('password_confirmation')->hidden(),
            BelongsToMany::make('topics')->type('topics')->hidden(),
            BelongsTo::make('designation', 'designation')->type('designations')->readOnly(),
            BelongsToMany::make('liked_post', 'likedPosts')->type('posts')->readOnly(),
            BelongsToMany::make('following', 'following')->type('users')->readOnly(),
            BelongsToMany::make('followers', 'followers')->type('users')->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
        ];
    }

    /**
     * Get the resource paginator.
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
}
