<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/2
 * Time: 17:21
 */

namespace App\Transformers\Authorization;


use App\Models\DbPassport\Role;
use App\Transformers\AppTransformer;

class RoleTransformer extends AppTransformer
{
    public function transform(Role $role){
        return [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'updatedAt' => $role->updatedAt->timestamp,
            'createdAt' => $role->createdAt->timestamp,
            'deletedAt' => $role->deletedAt->timestamp ?? null,
        ];
    }
}
