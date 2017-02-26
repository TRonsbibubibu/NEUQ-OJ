<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 17-2-26
 * Time: 下午4:59
 */
Route::post('/role/createRole','RoleController@createRole');
Route::post('/role/deleteRole','RoleController@deleteRole');
Route::post('/role/giveRoleTo','RoleController@giveRoleTo');
Route::post('/role/updateRole','RoleController@updateRole');