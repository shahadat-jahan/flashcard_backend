<?php

namespace App\JsonApi\Proxies;

use App\Models\User;
use LaravelJsonApi\Eloquent\Proxy;

class Profile extends Proxy
{
    /**
     * UserAccount constructor.
     */
    public function __construct(?User $user = null)
    {
        parent::__construct($user ?: new User);
    }

    public static function find($id)
    {
        return User::query()->findOrFail($id);
    }
}
