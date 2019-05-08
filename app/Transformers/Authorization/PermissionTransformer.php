<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/2
 * Time: 16:12
 */

namespace App\Transformers\Authorization;

use App\Models\DbPassport\Permission;
use App\Transformers\AppTransformer;

class PermissionTransformer extends AppTransformer
{
    public function transform(Permission $permission){
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'slug' => $permission->slug,
            'httpMethod' => $permission->httpMethod,
            'httpPath' => $permission->httpPath,
            'updatedAt' => $permission->updatedAt->timestamp,
            'createdAt' => $permission->createdAt->timestamp,
            'deletedAt' => $permission->deletedAt->timestamp ?? null,
        ];
    }
}
