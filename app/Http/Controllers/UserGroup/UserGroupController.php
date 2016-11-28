<?php

namespace NEUQOJ\Http\Controllers\UserGroup;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserGroup\UserGroupNotExistException;
use NEUQOJ\Services\UserGroupService;
use Illuminate\Support\Facades\Hash;
use NEUQOJ\Http\Controllers\Controller;

class UserGroupController extends Controller
{
    private $userGroupService;
    //
    public function __construct(UserGroupService $service)
    {
        $this->userGroupService = $service;
    }

    /**
     * 创建新用户组
     */
    public function createNewGroup(Request $request)
    {
        //表单检查
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100|string',
            'description' => 'max:512',
            'max_size' => 'required|integer|max:300',
            'password' => 'min:6|max:20',//明文显示
            'is_closed' => 'boolean'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'max_size' => $request->max_size,
            'password' => $request->password?bcrypt($request->password):null,
            'is_closed' => $request->is_closed
        ];

        $groupId = $this->userGroupService->createUserGroup($request->user,$data);

        //TODO 创建时应该尝试对指定的多个用户发送邀请



        return response()->json([
            "code" => 0,
            "data" => [
                "group_id" => $groupId
            ]
        ]);

    }
    /**
     * 加入用户组
     */

    public function joinGroup(Request $request,$groupId)
    {
        $group = $this->userGroupService->getGroupById($groupId);

        if($group == null)
            throw new UserGroupNotExistException();


        if($group->password == null)
            $this->userGroupService->joinGroupWithoutPassword($request->user,$group);
        else
        {
            $validator = Validator::make($request->all(), [
                'password' => 'required|max:20'
            ]);

            if ($validator->fails())
                throw new FormValidatorException($validator->getMessageBag()->all());
            $this->userGroupService->joinGroupByPassword($request->user,$group,$request->password);

            return response()->json([
                "code" => 0
            ]);
        }
    }

    /**
     * 分页的用户查询
     */

    public function getMembers(Request $request,$groupId)
    {
        $groupId = intval($groupId);

        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();


        $total_count = $this->userGroupService->getGroupMembersCount($groupId);

        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:1|max:50',
            'page' => 'integer|min:1|max:500'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size',10);
        $page = $request->input('page',1);


        if(!empty($total_count))
            $data = $this->userGroupService->getGroupMembers($groupId,$page,$size);
        else
            $data = null;

        return response()->json([
            "code" => 0,
            "data" => $data,
            "page_count" => ($total_count%$size)?intval($total_count/$size+1):($total_count/$size)
        ]);
    }
    /**
     *模糊搜索
     */
    public function searchGroups(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:1|max:50',
            'page' => 'integer|min:1|max:500',
            'keyword' => 'required|max:30'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size',10);
        $page = $request->input('page',1);

        $total_count = $this->userGroupService->searchGroupsCount($request->keyword);

        if($total_count > 0)
            $data = $this->userGroupService->searchGroupsBy($request->keyword,$page,$size);
        else
            $data = [];

        return response()->json([
            "code" => 0,
            "data" => $data,
            "page_count" => ($total_count%$size)?intval($total_count/$size+1):($total_count/$size)
        ]);
    }

    public function quitGroup(Request $request,$groupId)
    {
        //验证逻辑：用户应该在退出用户组之前先输入他的密码来验证
        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|max:255'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if(!Hash::check($request->password,$request->user->password))
            throw new PasswordErrorException();

        if(!$this->userGroupService->deleteUserFromGroup($request->user->id,$groupId))
            throw new InnerError("fail to delete user from group");

        return response()->json([
            "code" => 0
        ]);
    }

    public function changeOwner(Request $request,$groupId)
    {
        $groupId = intval($groupId);

        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|max:255',
            'newOwnerId' => 'required|integer'
        ]);

        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();
        if(!$this->userGroupService->isUser)

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId))
            throw new NoPermissionException();

        //检查密码
        if(!Hash::check($request->password,$request->user->password))
            throw new PasswordErrorException();

        if(!$this->userGroupService->changeGroupOwner($request->user->id))
            throw new InnerError("Fail to change owner");

        return response()->json([
            "code" => 0
        ]);

    }

    public function closeGroup(Request $request,$groupId)
    {
        $groupId = intval($groupId);

        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|max:255'
        ]);

        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId))
            throw new NoPermissionException();

        //检查密码
        if(!Hash::check($request->password,$request->user->password))
            throw new PasswordErrorException();

        if(!$this->userGroupService->closeGroup($groupId))
            throw new InnerError("Fail to close group");

        return response()->json([
            "code" => 0
        ]);
    }

    public function openGroup(Request $request,$groupId)
    {
        $groupId = intval($groupId);

        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|max:255'
        ]);

        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId))
            throw new NoPermissionException();

        //检查密码
        if(!Hash::check($request->password,$request->user->password))
            throw new PasswordErrorException();

        if(!$this->userGroupService->closeGroup($groupId))
            throw new InnerError("Fail to open group");

        return response()->json([
            "code" => 0
        ]);
    }
}