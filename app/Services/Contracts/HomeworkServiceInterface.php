<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/20
 * Time: 下午2:49
 */
namespace NEUQOJ\Services\Contracts;

use NEUQOJ\Repository\Models\User;

interface HomeworkServiceInterface
{
    function getProblem(int $groupId,int $problemNum);
    //基本获取函数
    function getAllHomework(int $page,int $size);
    function getHomework(int $id,array $columns=['*']);

    function getHomeworkBy(string $param,string $value,array $columns=['*']);

    //获取作业的首页（面板），组织有所有题目（不加tag），用户解题状态以及每个题目当前的ac/submit数量
    function getHomeworkIndex(int $userId = -1,int $HomeworkId);

    //一个小组内的所有作业列表（简略,没有设置分页）
    function getHomeworksInGroup(int $groupId);

    //添加，权限的检查放在controller里
    function addHomework(array $user,int $userGroupId,array $data,array $problems):int;

    //修改
    function updateHomeworkInfo(int $homeworkId,array $data):bool;

    //修改题目，请注意这里题目数组的格式， 并非只有id，还包含有score字段，注意处理
    function updateHomeworkProblem(int $homeworkId,array $problems):bool;

    function deleteHomework(User $user,int $homeworkId):bool;

    //辅助判断函数

    function isHomeworkExist(int $homeworkId):bool;

    function isUserHomeworkOwner(int $userId,int $homeworkId):bool;

    function canUserAccessHomework(int $userId,int $homeworkId):bool;

    //状态,根据用户的权限设定组装不同的内容
    function getHomeworkStatus(int $userId,int $homewrokId);

    //切记 这个rank的组织和contest不同，用户信息方面应该join上他们的组内名片信息。
    //这意味着redis缓存部分的对象要重新写。
    function getHomeworkRank(int $homeworkId);

    function submitProblem(int $userId,int $groupId,int $problemNum,array $data):int;
}