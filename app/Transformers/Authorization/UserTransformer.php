<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/2
 * Time: 19:08
 */

namespace App\Transformers\Authorization;


use App\Models\DbPassport\User;
use App\Transformers\AppTransformer;

class UserTransformer extends AppTransformer
{
    public function transform(User $user){
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'updatedAt' => $user->updatedAt->timestamp,
            'createdAt' => $user->createdAt->timestamp,
            'deletedAt' => $user->deletedAt->timestamp ?? null,
        ];
    }
}
