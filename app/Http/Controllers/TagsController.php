<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午7:02
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\TagNotExistException;
use NEUQOJ\Exceptions\TagsExistExceptios;
use NEUQOJ\Exceptions\TagsUnchangedExceptions;
use NEUQOJ\Services\TagsService;

class TagsController extends Controller
{


    public function createTag(Request $request, TagsService $tagsService)
    {
       //表单认证
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:45',
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }


        //判断创建的tag是否存在
        $tagId = $tagsService->tagsExisted($request->name);//若不存在id为-1
        if($tagId != -1)
            throw new TagsExistExceptios();

        $data = array(
            'name'=>$request->name
        );

        //创建tag会返回tag的id 创建失败会返回-1
        if(($tagsService->createTags($data))!=-1)
            return response()->json([
                'code' =>0
            ]);

    }
    public function deleteTag(TagsService $tagsService,int $tagId)
    {

        //判断要删除的tag是否存在
        if ($tagsService->getTagById($tagId)==null)
            throw new TagNotExistException();

        if($tagsService->deleteTags($tagId))
            return response()->json(
                [
                    'code'=>0
                ]
            );
    }
    public function giveTagTo(TagsService $tagsService,int $tagId,int $problemId)//直接用TagId给予问题标签
    {

            if($tagsService->hasTags($tagId,$problemId))//判断这道题是否已经有该标签了
                throw new TagsExistExceptios();
            else
                $tagsService->giveTagsTo($tagId,$problemId);

        return response()->json(
            [
                'code'=>0
            ]
        );
    }

    function updateTag(Request $request,TagsService $tagsService,int $tagId)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'name'=>'required|max:45'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        //判断要删除的tag是否存在
        if ($tagsService->getTagById($tagId)==null)
            throw new TagNotExistException();

        //判断要修改的tag内容是否存在,或者未改变
        $tagId = $tagsService->tagsExisted($request->name);
        if($tagId != -1)
            throw new TagsExistExceptios();

        if($tagsService->updateTags($tagId,$request->name))
            return response()->json(
                [
                    'code'=>0
                ]
            );
    }

    public function updateProblemTag(Request $request,TagsService $tagsService,int $tagId,int $problemId)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tags'=>'required|max:45',
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }


        //判断要删除的tag是否存在
        if ($tagsService->getTagById($tagId)==null)
            throw new TagNotExistException();


        if(($tagsService->updateProblemTag($tagId,$problemId,$request->tags)))
            return response()->json([
                'code'=>0
            ]);

    }

    public function createProblemTag(Request $request,TagsService $tagsService,int $problemId)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tags'=>'required|max:45',
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        if($tagsService->createProblemTag($problemId,$request->tags))
            return response()->json(
                [
                    'code'=>0
                ]
            );
    }

    public function deleteProblemTag(TagsService $tagsService,int $tagId,int $problemId)
    {

        if ($tagsService->deleteProblemTag($tagId,$problemId))
            return response()->json(
                [
                    'code'=>0
                ]
            );
    }

    public function getSameTagProblem(Request $request,TagsService $tagsService)
    {

        $validator = Validator::make($request->all(),[
            'tagId'=>'integer|required',
            'size'=>'integer|min:1',
            'page'=>'integer|min:1'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $tagId = $request->input('tagId');
        $size = $request->input('size',20);
        $page = $request->input('page',1);

        if($data = $tagsService->getSameTagProblemList($tagId,$page,$size))
            return response()->json(
                [
                    'code'=>0,
                    'data'=>$data
                ]
            );
    }

    public function getSameSourceProblem(Request $request,TagsService $tagsService)
    {
        $validator = Validator::make($request->all(),[
            'source'=>'required',
            'size'=>'integer|min:1',
            'page'=>'integer|min:1'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $source = $request->input('source');
        $size = $request->input('size',20);
        $page = $request->input('page',1);

        if($data = $tagsService->getSameSourceProblemList($source,$page,$size))
            return response()->json(
                [
                    'code'=>0,
                    'data'=>$data
                ]
            );

    }
}