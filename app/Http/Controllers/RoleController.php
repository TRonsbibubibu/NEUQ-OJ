<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-22
 * Time: 下午10:15
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Http\Request;
use League\Flysystem\Exception;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\PrivilegeNotExistException;
use NEUQOJ\Exceptions\RoleExistedException;
use NEUQOJ\Exceptions\RoleNotExistException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Services\PrivilegeService;
use NEUQOJ\Services\RoleService;
use NEUQOJ\Services\UserService;
use Validator;
class RoleController extends Controller
{

    public function test(Request $request)
    {
       dd($request->user['id']);
    }

    public function createRole(RoleService $roleService,Request $request,PrivilegeService $privilegeService)
    {
        /*
         * 表单验证
         */
        $validator = Validator::make($request->all(), [
            'role' => 'required|max:30',
            'privilegeId'=>'required',
            'description'=>'required|max:100',
            'userId'=>'required'
        ]);


        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        
        /*
         * 判断要增加的角色是否存在
         */
        if($roleService->roleExisted($request->get('role')))
            throw new RoleExistedException();

        /*
         * 判断操作者是否具有对应权限
         */
        //dd($request->user['id']);
        if(!($privilegeService->hasNeededPrivilege('operate-role',$request->userId)))
            throw new PrivilegeNotExistException();

        //$privilege = $privilegeService->getPrivilegeDetailBy('id',$request->privilegeId,['name']);
        $data = array(
            'role'=>$request->get('role'),
            'privilege'=>$request->privilegeId,
            'description'=>$request->get('description'),
        );
        if($roleService->createRole($data)!=-1)
            return response()->json([
                'code' => 0
            ]);
    }

    public function deleteRole(Request $request,RoleService $roleService,PrivilegeService $privilegeService)
    {
        /*
         * 表单验证
         */
        $validator = Validator::make($request->all(), [
            'role' => 'required|max:30',
            'userId'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        /*
         * 判断要删除的角色是否存在
         */

        if(!($role = $roleService->roleExisted($request->get('role'))))
            throw new RoleNotExistException();
            $roleId = $role->id;
        /*
        * 判断操作者是否具有对应权限
        */
        if(!($privilegeService->hasNeededPrivilege('operate-role',$request->user['id'])))
            throw new PrivilegeNotExistException();


        if($roleService->deleteRole($roleId))
            return response()->json([
                'code' => 0
            ]);
    }

    /*
     * 申请得过中间件　相应角色才可以操作
     */
    public function giveRoleTo(Request $request,RoleService $roleService,PrivilegeService $privilegeService)
    {


        $validator = Validator::make($request->all(), [
            'operatorId'=>'required',
            'userId' => 'required',
            'role'=>'required',
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        /*
        * 判断操作者是否具有对应权限
        */
        if(!($privilegeService->hasNeededPrivilege('operate-role',$request->operatorId)))
            throw new PrivilegeNotExistException();

        $role = $request->role;

        /*
         * 判断给予的角色是否存在
         */
        if(!($roleService->roleExisted($role)))
            throw new RoleNotExistException();




        if(($roleService->hasRole($request->userId,$role)))
            throw new RoleExistedException();

         if($roleService->giveRoleTo($request->userId,$role))
         {
             return response()->json(
                 [
                     'code'=> 0
                 ]
             );
         }

    }

    public function updateRole(Request $request,RoleService $roleService)
    {
        $validator = Validator::make($request->all(), [
            'roleId'=>'required',
            'name' => 'required',
            'description'=>'required',
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        if($roleService->updateRole(['id'=>$request->id],['name'=>$request->name,'description'=>$request->description]))
            return response()->json(
                ['code'=>0]
            );
    }


}