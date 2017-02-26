<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-19
 * Time: 上午5:07
 */

namespace NEUQOJ\Repository\Eloquent;

use NEUQOJ\Repository\Traits\InsertWithIdTrait;

class RoleRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Role";
    }
    use InsertWithIdTrait;
}