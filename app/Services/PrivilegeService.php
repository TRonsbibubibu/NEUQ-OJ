<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-26
 * Time: 下午8:07
 */

namespace NEUQOJ\Services;

use Illuminate\Support\Facades\DB;
use NEUQOJ\Exceptions\PrivilegeNotExistException;
use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\RolePriRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Eloquent\UsrPriRepository;

class PrivilegeService
{
    private $priRepo;
    private $rolePriRepo;
    private $userPriRepo;
    public function __construct(PrivilegeRepository $privilegeRepository,RolePriRepository $rolePriRepository,UsrPriRepository $usrPriRepository)
    {
        $this->priRepo = $privilegeRepository;
        $this->rolePriRepo = $rolePriRepository;
        $this->userPriRepo = $usrPriRepository;
    }

    public function getPrivilegeDetailBy(string $param,string $value,array $columns = ['*'])
    {
        return $this->priRepo->getBy($param,$value,$columns)->first();
    }


    /*
     * 获取角色对应的权利
     */
    public function getRolePrivilege(int $roleId,array $columns = ['*'])
    {
        return $this->rolePriRepo->getBy('role_id',$roleId,$columns);
    }

    /*
     * 赋予对应用户　对应权限
     */
    public function givePrivilegeTo($userId,$roleId)
    {

        $privilege = $this->userPriRepo->getBy('user_id',$userId,['privilege_id']);

        $arr = $this->getRolePrivilege($roleId,['privilege_id']);

        $content = [];
        //dd($privilege);

        foreach ($arr as $item)
        {
            $flag = 0;
            foreach ($privilege as $pitem)
            {

                if($pitem['privilege_id'] == $item['privilege_id'])
                    $flag = 1;
            }
            /*
           * 给予的新角色含有原有的权限 就标注
           */
            //dd($pitem);
            if($flag == 0)
            {
                array_push($content,[
                    'user_id'=>$userId,
                    'privilege_id'=>$item['privilege_id']
                ]);
            }


        }

        if(!($this->userPriRepo->insert($content)))
            return false;

        return true;
    }
    /*
     * 判断用户是否具有某项权利
     * 查user_pri_relation
     */
    public function hasNeededPrivilege(string $privilegeNeeded,$user_id)
    {

        $arr = $this->getPrivilegeDetailBy('name',$privilegeNeeded,['id']);

        if(!$arr)
            throw new PrivilegeNotExistException();


        $pri_id = $arr->id;

        $data = array(
            'user_id'=>$user_id,
            'privilege_id'=>$pri_id,
        );

        if(!($this->userPriRepo->getByMult($data)))
            return false;
        else
            return true;
    }
    public function isPrivilegeBelong($privilegeId):bool
    {
        if($this->userPriRepo->getBy('privilege_id',$privilegeId)->first())
            return true;
        else
            return false;
    }
    public function deletePrivilege(array $data)
    {
        return $this->priRepo->deleteWhere($data);
    }


}