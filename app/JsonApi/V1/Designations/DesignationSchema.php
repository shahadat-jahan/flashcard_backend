<?php

namespace App\JsonApi\V1\Designations;

use App\Models\Designation;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\SoftDelete;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\OnlyTrashed;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WithTrashed;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\SoftDeletes;

class DesignationSchema extends Schema
{
    use SoftDeletes;

    /**
     * The model the schema corresponds to.
     */
    public static string $model = Designation::class;

    /**
     * The schema type.
     */
    public static function type(): string
    {
        return 'designations';
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
            Str::make('name')->sortable(),
            Number::make('status')->sortable(),
            DateTime::make('createdAt', 'created_at')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
            SoftDelete::make('deletedAt'),
        ];
    }

    /**
     * Get the resource filters.
     */
    public function filters(): array
    {
        $list = [
            WhereIdIn::make($this),
            Where::make('name', 'name'),
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
