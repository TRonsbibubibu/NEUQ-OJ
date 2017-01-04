<?php
Route::post('/message/send','MessageController@sendMessage');
Route::post('/message/getMessages/{UserId}','MessageController@getUserMessages');
Route::get('/message/checkMessage/{MId}','MessageController@checkUserMessage');
Route::post('/message/getUnreadMessages/{UserId}','MessageController@getUserUnreadMessages');
Route::get('/message/getUnreadMessageCount/{userId}','MessageController@getUserUnreadMessageCount');
Route::get('/message/getMessageCount/{userId}','MessageController@getUserMessageCount');
Route::get('/message/deleteOwnMessage/{userId}/{MId}','MessageController@deleteOwnMessage');
Route::post('/message/deleteOwnMessage/{userId}/{MId}','MessageController@deleteOwnMessage');