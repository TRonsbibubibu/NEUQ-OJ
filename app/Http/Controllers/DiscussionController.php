<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\TopicNotExistException;
use NEUQOJ\Http\Requests;
use NEUQOJ\Services\DiscussionService;

class DiscussionController extends Controller
{

    private $discussionService;

    public function __construct(DiscussionService $discussionService)
    {
        $this->discussionService = $discussionService;
    }


    //topic控制
    public function addTopic(Request $request)
    {
        //表单检查
        $validator = Validator::make($request->all(),[
            'title' => 'required|max:100|string',
            'content' => 'required'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $data = [
            'title' => $request->title,
            'content' => $request->get('content'),
            //'problem_id' => $request->problem_id,
            'user_id' => $request->user->id,
            'father' => 0,
        ];

        if($this->discussionService->addTopic($data)) {
            return response()->json([
                "code" => 0,
                "data" => [
                    //'problem_id' => $topic->problem_id,
                    'user_id' => $request->user->id,
                    'title' => $request->title
                ],
            ]);
        }


    }

    public function deleteTopic(Request $request,$topicId)
    {
        $userId = $request->user->id;
        $topicId = intval($topicId);

        //检查是否为创帖者
        if($this->discussionService->isTopicCreator($topicId,$userId)) {
            if($this->discussionService->deleteTopic($topicId)) {
                return response()->json([
                    "code" => 0,
                ]);
            }
        } else {
            throw new InnerError("You are not the creator , Fail to delete topic.");
        }

    }

    public function updateTopic(Request $request,$topicId)
    {
        $userId = $request->user->id;
        $topicId = intval($topicId);

        $validator = Validator::make($request->all(),[
            'content' => 'required'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if($request->title != null) {
            $data = [
                'title' => $request->title,
                'content' => $request->get('content'),
                //'problem_id' => $request->problem_id,
                'user_id' => $request->user->id,
            ];
        } else {
            $data = [
                'content' => $request->get('content'),
                //'problem_id' => $request->problem_id,
                'user_id' => $request->user->id,
            ];
        }

        //检查是否为创帖者
        if($this->discussionService->isTopicCreator($topicId,$userId)) {
            if($this->discussionService->updateTopic($topicId,$data))
                return response()->json([
                    "code" => 0,
                ]);
        } else {
            throw new InnerError("You are not the creator , Fail to update topic.");
        }
    }


    //文章查询，支持模糊查询
    public function searchTopic(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'size' => 'required|min:1|max:50',
            'page' => 'required|min:1|max:50',
            'title' => 'required|max:30'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBags()->all());

        $size = $request->input('size',10);
        $page = $request->input('page',1);

        $total_count = $this->discussionService->searchTopicCount($request->title);

        if($total_count > 0)
            $data = $this->discussionService->searchTopicByTitle($request->title,$page,$size);
        else
            $data = [];

        return response()->json([
            "code" => 0,
            "data" => $data,
            "page_count" => ($total_count%$size)?intval($total_count/$size+1):($total_count/$size)
        ]);
    }

    //回复控制
    public function addReply(Request $request,int $father)
    {
        //表单检查
        $validator = Validator::make($request->all(),[
            'content' => 'required'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $data = [
            'content' => $request->get('content'),
            //'problem_id' => $request->problem_id,
            'user_id' => $request->user->id,
            'father' => $father,
        ];

        if($this->discussionService->addTopic($data)) {
            return response()->json([
                "code" => 0,
                "data" => [
                    //'problem_id' => $topic->problem_id,
                    'user_id' => $request->user->id,
                    'father' => $request->father
                ],
            ]);
        }
    }

    public function deleteReply(Request $request, $replyId)
    {
        $userId = $request->user->id;
        $replyId = intval($replyId);
        $topic = $this->discussionService->searchTopicById($replyId);

        //检查是否为一个reply
        if(!$this->discussionService->isReply($replyId))
            throw new InnerError("This is not a reply");

        //检查是否为回复人
        if(!$this->discussionService->isCreator($replyId,$userId) && !$this->discussionService->isCreator($topic->id,$userId))
            throw new InnerError("You have no privilege to do that");

        if($this->discussionService->deleteReply($replyId))
            return response()->json([
                'code' => 0
            ]);
    }


    //置顶控制
    public function stick(Request $request, int $topicId)
    {
        if($this->discussionService->stick($topicId)) {
            return response()->json([
                'code' => 0,
            ]);
        }
    }

    public function unstick(Request $request, int $topicId)
    {
        if($this->discussionService->unStick($topicId)) {
            return response()->json([
                'code' => 0,
            ]);
        }
    }

}
