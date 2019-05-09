<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/5/9
 * Time: 14:25
 */

namespace App\Transformers\Authorization;


use App\Models\DbPassport\Service;
use App\Transformers\AppTransformer;

class ServiceTransformer extends AppTransformer
{
    public function transform(Service $service){
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->description,
            'updatedAt' => $service->updatedAt->timestamp,
            'createdAt' => $service->createdAt->timestamp,
            'deletedAt' => $service->deletedAt->timestamp ?? null,
        ];
    }
}
