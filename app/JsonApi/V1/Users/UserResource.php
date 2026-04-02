<?php

namespace App\JsonApi\V1\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LaravelJsonApi\Core\Resources\JsonApiResource;

/**
 * @property User $resource
 */
class UserResource extends JsonApiResource
{
    /**
     * Get the resource's attributes.
     *
     * @param  Request|null  $request
     */
    public function attributes($request): iterable
    {
        $passwordToken = DB::table('password_reset_tokens')->where('email', $this->resource->email)->first();

        $data = [
            'name' => $this->resource->name,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'email' => $this->resource->email,
            'avatar' => empty($this->resource->avatar) ? null : config('filesystems.disks.public.url').'/'.($this->resource->avatar),
            'status' => $this->resource->status,
            'role' => $this->resource->role,
            'emailVerified_at' => $this->resource->email_verified_at,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'total_following' => $this->resource->following_count,
            'total_followers' => $this->resource->followers_count,
            'total_liked_post' => $this->resource->liked_posts_count,
            'password_token_created_at' => $passwordToken ? $passwordToken->created_at : null,
        ];

        if (isset($this->resource->posts_count)) {
            $data['total_posts'] = $this->resource->posts_count;
        }

        return $data;
    }

    /**
     * Get the resource's relationships.
     *
     * @param  Request|null  $request
     */
    public function relationships($request): iterable
    {
        return [
            $this->relation('designation')->withoutLinks(),
            $this->relation('topics')->withoutLinks(),
            $this->relation('likedPosts')->withoutLinks(),
            $this->relation('following')->withoutLinks(),
            $this->relation('followers')->withoutLinks(),
        ];
    }
}
