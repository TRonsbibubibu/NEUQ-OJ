<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/1/7
 * Time: 下午10:50
 */

Route::post('/topic/search','DiscussionController@searchTopic');

Route::group(['middleware' => 'token'], function() {
    Route::post('/topic/create','DiscussionController@addTopic');
    Route::post('/topic/delete/{id}','DiscussionController@deleteTopic');
    Route::post('/topic/update/{id}','DiscussionController@updateTopic');

    Route::post('/reply/create/{father}','DiscussionController@addReply');

    Route::post('/topic/stick/{id}','DiscussionController@stick');
    Route::post('/topic/unstick/{id}','DiscussionController@unstick');
});