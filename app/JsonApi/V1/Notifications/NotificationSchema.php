<?php

namespace App\JsonApi\V1\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\Boolean;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class NotificationSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     */
    public static string $model = DatabaseNotification::class;

    /**
     * The schema type.
     */
    public static function type(): string
    {
        return 'notifications';
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
            Id::make()->uuid(),
            Str::make('type'),
            Str::make('data'),
            Boolean::make('readAt', 'read_at')->sortable(),
            DateTime::make('createdAt', 'created_at')->sortable()->readOnly(),
            DateTime::make('updatedAt', 'updated_at')->sortable()->readOnly(),
            BelongsTo::make('notifiable')->type('users')->readOnly(),
        ];
    }

    public function sortables(): array
    {
        return ['created_at', 'updated_at'];
    }

    /**
     * Get the resource paginator.
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make()->withoutNestedMeta();
    }
}
