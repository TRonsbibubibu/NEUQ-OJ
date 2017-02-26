<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-15
 * Time: 下午11:59
 */


Route::post('/tag/updateTag/{Tid}','TagsController@updateTag');
Route::post('/tag/createTag','TagsController@createTag');
Route::get('/tag/deleteTag/{Tid}','TagsController@deleteTag');
Route::post('/tag/createProblemTag/{Pid}','TagsController@createProblemTag');
Route::get('/tag/{Tid}/deleteProblemTag/{Pid}','TagsController@deleteProblemTag');
Route::post('/tag/{Tid}/updateProblemTag/{Pid}','TagsController@updateProblemTag');
Route::post('/tag/{Tid}/updateTag','TagsController@updateProblemTag');
Route::post('/tag/{Tid}/giveTagToProblem/{Pid}','TagsController@giveTagTo');
Route::get('/tag/getSameTagProblem','TagsController@getSameTagProblem');
Route::get('/tag/getSameSourceProblem','TagsController@getSameSourceProblem');
