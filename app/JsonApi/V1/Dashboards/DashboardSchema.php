<?php

namespace App\JsonApi\V1\Dashboards;

use App\Entities\Dashboard;
use LaravelJsonApi\Core\Schema\Schema;
use LaravelJsonApi\NonEloquent\Fields\Attribute;

class DashboardSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     */
    public static string $model = Dashboard::class;

    /**
     * The schema type.
     */
    public static function type(): string
    {
        return 'dashboard';
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): iterable
    {
        return [
            Attribute::make('draft'),
            Attribute::make('pending'),
            Attribute::make('approved'),
            Attribute::make('declined'),
            Attribute::make('total'),
        ];
    }
}
