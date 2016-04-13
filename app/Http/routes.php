<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group([
    'middleware' => 'ApartmentApi\Http\Middleware\AclMiddleware'
], function()
{
    Route::group([
        'middleware' => 'ApartmentApi\Http\Middleware\ValidOAuth',
    ], function() {
        // User Management
        get('/user/list', [ 'as' => 'user.list', 'uses' => 'UserController@getUsersList']);
        post('/user/create', [ 'as' => 'user.create', 'uses' => 'V1\UserController@create']);
    });

    // Society Documents


    Route::post('/admin_file/society/upload',[ 'as' => 'society_document.create', 'uses' => 'AdminFileController@uploadSocietyDocument']);

    get('admin_file/listsocietydocuments',[ 'as' => 'society_document.list', 'uses' => 'AdminFileController@listSocietyDocuments']);
    post('/admin_file/society/upload',[ 'as' => 'society_document.create', 'uses' => 'AdminFileController@uploadSocietyDocument']);

    // Meeting
    post('/meeting/create', [ 'as' => 'meeting.create', 'uses' => 'MeetingController@create']);
    post('/meeting/update/{id}', [ 'as' => 'meeting.update', 'uses' => 'MeetingController@edit']);
    get('/meeting/list', [ 'as' => 'meeting.list', 'uses' => 'MeetingController@getMeetingsList']);

	Route::group([
    'middleware' => 'ApartmentApi\Http\Middleware\CheckOAuthToken',
    'prefix'     => 'v1',
    'namespace'  => 'V1',
	], function()
	{
		post('/type/update',[ 'as' => 'type.create', 'uses' => 'CategoryController@adminSaveOrUpdateType'] );
	});

});

// CONVERSATIONS
Route::get('/post/list', [ 'as' => 'post.list', 'uses' => 'EntityController@postList']);
Route::post('/post/create', [ 'as' => 'post.create', 'uses' => 'EntityController@storeOrUpdatePost']);
Route::post('/post/update', [ 'as' => 'post.update', 'uses' => 'EntityController@storeOrUpdatePost']);
Route::delete('/post/delete/{id}', [ 'as' => 'post.delete', 'uses' => 'EntityController@delete']);
Route::get('/post/{id}', [ 'as' => 'post', 'uses' => 'EntityController@item']);
Route::post('/reply/create', [ 'as' => 'reply.create', 'uses' => 'EntityController@storeReply']);
Route::get('/reply/list/{postId}', [ 'as' => 'reply.list', 'uses' => 'EntityController@replyList']);
Route::post('/like/create', [ 'as' => 'like.create', 'uses' => 'EntityController@storeLike']);

// GROUPS

Route::post('/group/create','EntityController@storeOrUpdateGroup');
//Route::post('/group/update','EntityController@storeOrUpdateGroup');
Route::get('/group/list','EntityController@groupList');
Route::post('/group/join', 'UserGroupController@addUserToGroup');
Route::get('/group/edit/{id}','EntityController@edit');
Route::get('/group/delete/{id}','EntityController@delete');
Route::get('/group/{ }','EntityController@getGroup');
Route::post('/group/post/create','EntityController@addPostToGroup');
Route::get('/group/post/list/{parent_id}', 'EntityController@postList');

// USERS

Route::post('/user/update/{id}', [ 'as' => 'user.update', 'uses' => 'UserController@update']);
Route::post('/user/deactivate', [ 'as' => 'user.deactivate', 'uses' => 'UserController@deactivate']);
Route::post('/user/activate', [ 'as' => 'user.activate', 'uses' => 'UserController@activate']);
Route::post('/user/approve', [ 'as' => 'user.approve', 'uses' => 'UserController@approve']);

Route::get('/user/data', [ 'as' => 'users.lists', 'uses' => 'UserController@getAllUsers']);
Route::get('/user/find', [ 'as' => 'user', 'uses' => 'UserController@findUser']);
Route::get('user/info', [ 'as' => 'user.info', 'uses' => 'UserController@getUser_info']);
Route::get('/user/{id}', [ 'as' => 'user', 'uses' => 'UserController@getUser']);

Route::get('/society_user/{id}', [ 'as' => 'society.user', 'uses' => 'UserController@getSocietyUser']);
Route::post('/user/check_pwd', [ 'as' => 'user.check_pwd', 'uses' => 'UserController@check_pwd']);
Route::post('/user/update_pwd',['as' => 'user.update_pwd', 'uses' => 'UserController@update_pwd']);

Route::post('/user/reset_forgotpwd', [ 'as' => 'user.fgt_pwd', 'uses' => 'UserController@reset_forgotPwd']);
Route::post('/user/edit_info/{id}', [ 'as' => 'user.edit_info', 'uses' => 'UserController@editUser_info']);


// Notice

Route::get('/notice/list', [ 'as' => 'notice.list', 'uses' => 'NoticeBoardController@index']);
Route::get('/notice/print/{itemId}', [ 'as' => 'notice.print', 'uses' => 'V1\PdfController@noticeBoard']);
Route::get('/notice/expired', [ 'as' => 'notice.expired', 'uses' => 'NoticeBoardController@getExpired']);
Route::get('/notice/{id}', [ 'as' => 'notice', 'uses' => 'NoticeBoardController@item']);
Route::post('/notice/edit/{id}', [ 'as' => 'notice.edit', 'uses' => 'NoticeBoardController@edit']);
Route::post('/notice/create', [ 'as' => 'notice.create', 'uses' => 'NoticeBoardController@create']);
Route::get('/notice/resi/list', [ 'as' => 'notice.list', 'uses' => 'NoticeBoardController@getresinotices']);
Route::get('/notice/attendee/{id}', [ 'as' => 'notice.attendee.list', 'uses' => 'NoticeBoardController@noticeattendee']);


// Documents

Route::get('/folder/list', [ 'as' => 'folder.list', 'uses' => 'DocumentController@folderList']);
Route::get('/folder/allData', [ 'as' => 'folder.allData', 'uses' => 'DocumentController@getAllResidentFolders']);
Route::get('/document/official/folderlist',['as'=>'folder.official.list','uses'=>'DocumentController@adminFolderList'] );
Route::post('/folder/create', [ 'as' => 'folder.create', 'uses' => 'DocumentController@createFolder']);
Route::post('/folder/update/{id}', [ 'as' => 'folder.update', 'uses' => 'DocumentController@updateFolder']);
Route::get('/folder/{id}', [ 'as' => 'folder', 'uses' => 'DocumentController@folder']);
Route::post('/document/create', [ 'as' => 'document.create', 'uses' => 'DocumentController@createDocument']);
Route::post('/flat/document/create', [ 'as' => 'document.flat', 'uses' => 'DocumentController@createFlatDocument']);
Route::post('/document/delete', [ 'as' => 'document.delete', 'uses' => 'DocumentController@deleteDocument']);
Route::post('/flat_documents/delete',['as'=>'flat_document.delete','uses'=>'DocumentController@deleteFlatDocument']);
Route::post('/document/update/{id}', [ 'as' => 'document.update', 'uses' => 'DocumentController@updateDocument']);
Route::get('/document/list', [ 'as' => 'document.list', 'uses' => 'DocumentController@documentList']);
Route::get('flat/document/reminder',['as'=>'flat_document.reminder','uses'=>'DocumentController@FlatDocumentReminder']);
Route::get('flat/document/list/{flat_id}', [ 'as' => 'document.flat_list', 'uses' => 'DocumentController@FlatDocumentList']);
Route::get('/document/official/list', ['as'=>'document.official.list' ,'uses'=>'DocumentController@adminDocumentList']);
Route::post('/resident_folder/delete', [ 'as' => 'resident_folder.delete', 'uses' => 'DocumentController@deleteResidentFolder']);
Route::get('/document/{id}', [ 'as' => 'folder', 'uses' => 'DocumentController@document']);
Route::get('flat/document/{file_id}', [ 'as' => 'file', 'uses' => 'DocumentController@FlatDocument']);
Route::post('update/flat/document/{id}',['as' =>'update.document', 'uses'=>'DocumentController@updateFlatDocument']);

//MEETING
Route::get('/meeting/alerts', [ 'as' => 'meeting.alerts', 'uses' => 'MeetingController@sendAlerts']);

Route::get('/meeting/invitees/{id}',['as'=>'meeting.send_invitees','uses'=>'MeetingController@SendManualInvitees']);

Route::delete('/meeting/delete/{id}', [ 'as' => 'meeting.delete', 'uses' => 'MeetingController@delete']);

Route::get('/oldmeeting/list', [ 'as' => 'oldmeeting.list', 'uses' => 'MeetingController@oldMeeting']);
Route::get('/meeting/{id}', [ 'as' => 'meeting', 'uses' => 'MeetingController@getMeeting']);


//MEETING ATTENDEES

Route::post('/meeting_attendee/create', [ 'as' => 'meeting_attendee.create', 'uses' => 'MeetingAttendeeController@updateOrCreate']);
Route::post('/meeting_attendee/update/{id}', [ 'as' => 'meeting_attendee.update', 'uses' => 'MeetingAttendeeController@updateOrCreate']);
Route::delete('/meeting_attendee/delete/{id}', [ 'as' => 'meeting_attendee.delete', 'uses' => 'MeetingAttendeeController@delete']);
Route::get('/meeting_attendee/list', [ 'as' => 'meeting_attendee.list', 'uses' => 'MeetingAttendeeController@getAttendeesList']);
Route::get('/meeting_attendee/{id}', [ 'as' => 'meeting_attendee', 'uses' => 'MeetingAttendeeController@getAttendee']);


//TASK CATEGORY

Route::post('/task_category/create', [ 'as' => 'task_category.create', 'uses' => 'TaskCategoryController@createCategory']);
Route::post('/task_category/update/{id}', [ 'as' => 'task_category.update', 'uses' => 'TaskCategoryController@updateCategory']);
Route::get('/task_category/list', [ 'as' => 'task_category.create', 'uses' => 'TaskCategoryController@getCategoriesList']);
Route::get('/task_category/allData', [ 'as' => 'task_category.data', 'uses' => 'TaskCategoryController@getAllCategories']);
Route::get('/task_category/{id}', [ 'as' => 'task_category', 'uses' => 'TaskCategoryController@getCategory']);
Route::post('/task_category/check_category',[ 'as' => 'task_category' , 'uses' => 'TaskCategoryController@check_category']);


//TASK
//Route::post('/task/create','TaskController@updateOrCreate');

Route::post('/task/create', [ 'as' => 'task.create', 'uses' => 'TaskController@Create']);
Route::post('/task/edit/{id}', [ 'as' => 'task_category.edit', 'uses' => 'TaskController@edit']);
Route::get('/task/list', [ 'as' => 'task.list', 'uses' => 'TaskController@getTasksList']);
Route::get('/task/{id}', [ 'as' => 'task', 'uses' => 'TaskController@getTask']);
Route::get('/mytasks/list', [ 'as' => 'mytasks.list', 'uses' => 'TaskController@getMyTasks']);
Route::post('/mytasks/close', [ 'as' => 'mytasks.close', 'uses' => 'TaskController@close']);
Route::get('/oldtasks/list', [ 'as' => 'oldtasks.list', 'uses' => 'TaskController@OldTasks']);

//ADMIN FOLDER

Route::post('/admin_folder/create', [ 'as' => 'admin_folder.create', 'uses' => 'AdminFolderController@create']);
Route::post('/admin_folder/delete', [ 'as' => 'admin_file.delete', 'uses' => 'AdminFolderController@delete']);
Route::post('/admin_folder/update/{id}', [ 'as' => 'admin_folder.update', 'uses' => 'AdminFolderController@update']);
Route::get('/admin_folder/list', [ 'as' => 'admin_folder.list', 'uses' => 'AdminFolderController@getFoldersList']);
Route::get('/admin_folder/allData', [ 'as' => 'admin_folder.all_data', 'uses' => 'AdminFolderController@getAllFolders']);
Route::get('/admin_folder/{id}', [ 'as' => 'admin_folder', 'uses' => 'AdminFolderController@getFolder']);
Route::get('/flat/folder/list', [ 'as' => 'flat_folder.list', 'uses' => 'AdminFolderController@getFlatFolders']);

// AMENITIES

//ADMIN FILE

Route::post('/admin_file/create', [ 'as' => 'admin_file.create', 'uses' => 'AdminFileController@create']);

Route::get('/admin_file/mandatoryFile/{type}','AdminFileController@getFileMandatoryDetails');
Route::get('/admin_file/search', [ 'as' => 'society.search', 'uses' => 'AdminFileController@search']);
//Route::get('admin_file/documents/{id}','AdminFileController@listSocietyDocuments');

Route::post('/admin_file/delete', [ 'as' => 'admin_file.delete', 'uses' => 'AdminFileController@delete']);
Route::get('admin_file/listsocietydocuments',[ 'as' => 'society_document.list', 'uses' => 'AdminFileController@listSocietyDocuments']);
Route::get('/admin_file/list', [ 'as' => 'admin_file.list', 'uses' => 'AdminFileController@getFilesList']);
Route::get('/admin_file/{id}', [ 'as' => 'admin_file', 'uses' => 'AdminFileController@getFile']);
Route::post('/admin_file/update/{id}', [ 'as' => 'admin_file.update', 'uses' => 'AdminFileController@update']);
Route::get('/admin_file/societyDocument/{id}','AdminFileController@getSocietyDocumentFile');
Route::get('/flat_file/list', [ 'as' => 'flat_file.list', 'uses' => 'AdminFileController@getFlatFiles']);
//ALBUM

Route::post('/album/create', [ 'as' => 'album.create', 'uses' => 'AlbumController@create']);
Route::post('/album/upload', [ 'as' => 'album.upload', 'uses' => 'AlbumController@upload']);
Route::post('/album/update/{id}', [ 'as' => 'album.update', 'uses' => 'AlbumController@update']);
Route::post('/album/delete', [ 'as' => 'album.delete', 'uses' => 'AlbumController@delete']);
Route::post('/album/photo/delete', [ 'as' => 'album.photo.delete', 'uses' => 'AlbumController@deletePhoto']);
Route::get('/album/list', [ 'as' => 'album.photo.delete', 'uses' => 'AlbumController@getAlbums']);
Route::get('/album/photos/{id}', [ 'as' => 'album.photo.delete', 'uses' => 'AlbumController@photos']);
Route::get('/album/{id}', [ 'as' => 'album.photo.delete', 'uses' => 'AlbumController@getAlbum']);

//SOCIETY BLOCK

Route::post('/block/create', [ 'as' => 'block.create', 'uses' => 'BlockController@create']);
Route::post('/block/update/{id}', [ 'as' => 'block.update', 'uses' => 'BlockController@update']);
Route::post('/block/delete', [ 'as' => 'block.delete', 'uses' => 'BlockController@delete']);
Route::get('/block/list', [ 'as' => 'block.list', 'uses' => 'BlockController@getBlockList']);
Route::get('/block/allData', [ 'as' => 'block.data', 'uses' => 'BlockController@getAllBlock']);
Route::get('/block/{id}', [ 'as' => 'block', 'uses' => 'BlockController@getBlock']);

// SOCIETY

Route::post('/society/create', [ 'as' => 'society.create', 'uses' => 'SocietyController@create']);
Route::post('/society/join', [ 'as' => 'society.join', 'uses' => 'SocietyController@join']);
Route::get('/society/list', [ 'as' => 'society.list', 'uses' => 'SocietyController@listAction']);
Route::get('/society/join/buildings/{societyId}', [ 'as' => 'society.join.buildings', 'uses' => 'SocietyController@buildings']);
Route::get('/society/join/building/blocks/{buildingId}', [ 'as' => 'society.join.building.blocks', 'uses' => 'SocietyController@blocks']);
Route::post('/society/switch', [ 'as' => 'society.switch', 'uses' => 'SocietyController@switchSociety']);
Route::get('/society/search', [ 'as' => 'society.search', 'uses' => 'SocietyController@search']);
Route::get('/society_info', [ 'as' => 'society.society_info', 'uses' => 'SocietyController@getSocietyInfo']);
Route::post('/update/society_info', [ 'as' => 'society.update_society_info', 'uses' => 'SocietyController@UpdateSocietyInfo']);
Route::post('/checkemail',['as'=>'checkemail','uses'=>'SocietyController@checkEmailexists']);

//FORUM TOPIC

Route::post('/adminforum/topic/save', [ 'as' => 'adminforum.topic.save', 'uses' => 'EntityController@storeOrUpdateForumTopic']);
Route::post('/adminforum/topic/reply/save', [ 'as' => 'adminforum.topic.reply.save', 'uses' => 'EntityController@storeReplyForumTopic']);
Route::get('/adminforum/topic/list', [ 'as' => 'adminforum.topic.lists', 'uses' => 'AdminForumController@getTopicList']);
Route::get('/adminforum/topic/count', [ 'as' => 'adminforum.topic.count', 'uses' => 'AdminForumController@getTopicCount']);
Route::get('/adminforum/topic/{id}', [ 'as' => 'adminforum.topic', 'uses' => 'TopicController@getTopicDetails']);
Route::get('/adminforum/topic/reply/{id}', [ 'as' => 'adminforum.topic.reply', 'uses' => 'TopicController@getReplyList']);
Route::post('/adminforum/topic/upload', [ 'as' => 'adminforum.topic.upload', 'uses' => 'EntityController@uploadAdminForumFiles']);


//CALENDAR EVENT


Route::post('/event/save', [ 'as' => 'event.save', 'uses' => 'CalendarController@storeOrUpdateEvent']);
Route::post('/event/delete/{id}', [ 'as' => 'event.delete', 'uses' => 'CalendarController@deleteEvent']);
Route::get('/event/list', [ 'as' => 'event.list', 'uses' => 'CalendarController@getEventList']);


// MEMBER and Flat

Route::get('/flat/list', [ 'as' => 'flats', 'uses' => 'FlatController@getFlats']);
Route::get('/user/flat/list/{id}','FlatController@getUserFlats');
Route::get('/user/flat/{id}', [ 'as' => 'user.flat', 'uses' => 'FlatController@getUserFlat']);
Route::get('/member/list/{id}', [ 'as' => 'member.list', 'uses' => 'FlatController@members']);
//Route::post('/member/create', function(\Illuminate\Http\Request $request, $id = null) {
//    if ($request->get('action') == 'add') {
//        return app()->make('FlatController@createMember');
//    }
//
//    if ($request->get('action') == 'update') {
//        return FlatController::updateMember($request, $id);
//    }
//}); //[ 'as' => 'member.create', 'uses' => 'FlatController@createMember']
Route::post('/member/create',['as' => 'member.create', 'uses' => 'FlatController@createMember']);
Route::post('/member/update/{id}',['as' => 'member.create', 'uses' => 'FlatController@updateMember']);
Route::post('/associatemember/create', [ 'as' => 'member.create', 'uses' => 'FlatController@createAssociateMember']);
Route::get('/associatemember/{id}', [ 'as' => 'associatemember.list', 'uses' => 'FlatController@getAssociateMember']);
Route::post('/member/delete', [ 'as' => 'member.delete', 'uses' => 'FlatController@deleteMember']);
Route::post('/associatemember/delete/{id}', [ 'as' => 'associatemember.delete', 'uses' => 'FlatController@deleteAssociateMember']);
Route::get('/flat/{id}', [ 'as' => 'flat', 'uses' => 'FlatController@getFlat']);
Route::get('/member/{id}', [ 'as' => 'member', 'uses' => 'FlatController@getMember']);
Route::post('/flat/edit/{id}', [ 'as' => 'flat.edit', 'uses' => 'FlatController@updateFlat']);
Route::post('/flats/update', [ 'as' => 'flat.edit', 'uses' => 'FlatController@updateFlats']);
Route::post('/user/flat/update/{id}', [ 'as' => 'user.flat.update', 'uses' => 'FlatController@updateUserFlat']);
Route::post('/flat/admin/add', [ 'as' => 'flat.admin.add', 'uses' => 'FlatController@addAdminFlat']);
Route::post('/user/addflat', [ 'as' => 'user.flat.add', 'uses' => 'FlatController@AddUserFlat']);


// HelpDESK TICKET

Route::post('/helpdesk/ticket/save', ['as'=>'helpdesk.ticket.save','uses'=>'HelpDeskController@createOrUpdate']);
Route::get('/helpdesk/ticket/list', ['as'=>'helpdesk.ticket.list','uses'=>'HelpDeskController@ticketList']);
Route::get('/helpdesk/ticket/count','HelpDeskController@ticketCount');
Route::get('/helpdesk/ticket/{id}', ['as'=>'helpdesk.ticket.item','uses'=>'HelpDeskController@ticket']);
Route::get('/helpdesk/category/list', ['as'=>'helpdesk.category.list','uses'=>'HelpDeskController@categoryList']);
Route::get('/helpdesk/all_category/list', ['as'=>'helpdesk.category.list','uses'=>'HelpDeskController@All_categoryList']);
Route::post('/helpdesk/category/save', ['as'=>'helpdesk.category.save','uses'=>'HelpDeskController@createOrUpdateCategory']);
Route::post('/helpdesk/ticket/{ticketId}/note/save', ['as'=>'helpdesk.note.save','uses'=>'HelpDeskController@saveTicketNote']);
Route::post('/helpdesk/ticket/{ticketId}/admin_note/save', ['as'=>'helpdesk.admin_note.save','uses'=>'HelpDeskController@saveAdminTicketNote']);
Route::get('/helpdesk/ticket/{ticketId}/note/list', ['as'=>'helpdesk.note.list','uses'=>'HelpDeskController@ticketNotes']);


// LOGIN AND ACCESSTOKEN

Route::post('/getAccessToken', [ 'as' => 'accesstoken', 'uses' => 'OAuthController@issueAccessToken']);
Route::post('/superadmin/login', [ 'as' => 'superadmintoken', 'uses' => 'OAuthController@superAdminlogin']);
Route::post('/getpermissiontype', [ 'as' => 'getpermissiontype', 'uses' => 'OAuthController@getPermissionType']);

// Other

Route::post('/society/checkemail', [ 'as' => 'society.checkemail', 'uses' => 'SocietyController@checkEmail']);
Route::post('/society/checkflat', [ 'as' => 'society.checkflat', 'uses' => 'SocietyController@checkFlat']);
Route::post('/society/check_useremail', [ 'as' => 'society.verifymail', 'uses' => 'SocietyController@verifyEmail']);



// ACL

//Route::get('/acl/resource/list',['as' => 'acl.resource.list','uses' => 'AclController@resourceList' ]);
Route::post('/acl/resource/add','AclController@addResource');
Route::post('/acl/permission/add','AclController@addPermissionResource');
Route::post('/acl/user/role/add','AclController@addOrUpdateAclUserRole');
Route::get('/acl/user/role/list','AclController@userRoles');
//Route::get('/acl/rolepermission/list','AclController@getRolePermissions');
//Route::get('/acl/userpermission/list','AclController@getModules');
Route::get('/acl/rolemoduleaccess/list','AclController@getModules');
Route::get('/acl/rolemodulepermission/list','AclController@getRoleModulePermissions');
Route::post('/acl/role/add','AclController@saveRole');
Route::get('/acl/role/list','AclController@roleList');
Route::get('/acl/user/list',['as'=>'acl.user.list','uses'=>'AclController@userList']);
Route::post('/acl/role/moduleaccess/add','AclController@addOrUpdateRoleModuleAccess');
Route::post('/acl/role/modulepermission/add','AclController@addOrUpdateRoleModulePermission');
Route::post('/acl/user/modulepermission/add','AclController@addOrUpdateUserPermission');
Route::post('/acl/user/set_module_access','AclController@addUserModuleAccess');
Route::post('/acl/role/delete','AclController@deleteRole');
Route::post('/acl/role/assign','AclController@checkRoleAssign');
Route::post('/acl/role/nameupdate','AclController@editRoleName');

// Superadmin
Route::get('/superadmin/list/society', [ 'as' => 'superadmin.society', 'uses' => 'SuperAdminController@listSociety']);
Route::post('/superadmin/user/update_module_access',['as' => 'superadmin.user.update_module_access','uses'=>'SuperAdminController@addUserModuleAccess']);
Route::get('acl/resource/list','SuperAdminController@getModulePermissions');




// Request without middleware
Route::group([
    'prefix'     => 'v1',
    'namespace'  => 'V1'
], function()
{
    get('cities', 'CityController@index');

    get('states', 'StateController@index');
    get('types' , 'CategoryController@defaultSuperAdminTypeShow');
    get('division','DivisionController@listDivision');
    get('region','RegionController@index');
    get('district','DistrictController@index');
    get('district/search','DistrictController@search');
    get('division/search','DivisionController@search');
    get('region/search','RegionController@search');
    get('states/div/{id}','DivisionController@listStateDivision');
    get('division/reg/{id}','DistrictController@listDivisionRegion');

});

// Request which are required for super admin oauth token
Route::group([
    'middleware' => 'ApartmentApi\Http\Middleware\ValidSuperAdminOAuth',
    'prefix'     => 'v1',
    'namespace'  => 'V1',
], function()
{
    // State
    post('state', 'StateController@store');
    get('state/{id}', 'StateController@show');
    post('state/update/{id}', 'StateController@update');
    post('state/{id}', 'StateController@destroy');

    // City
    get('city/{id}', 'CityController@show');
    post('city/update/{id}', 'CityController@update');
    post('city/{id}', 'CityController@destroy');
    //post('city', 'CityController@store');
    post('city', 'CityController@store');

    //division

    get('division/delete/{id}','DivisionController@destroy');
    post('division/save','DivisionController@store');
    get('division/edit/{id}','DivisionController@show');
    post('division/check','DivisionController@CheckDuplicateDivision');

    //region

    post('region/save','RegionController@store');
    get('region/delete/{id}','RegionController@destroy');
    get('region/edit/{id}','RegionController@show');
    post('region/check','RegionController@CheckDuplicateRegion');

    //District

    post('district/save','DistrictController@store');
    get('district/edit/{id}','DistrictController@show');
    get('district/delete/{id}','DistrictController@destroy');
    post('district/check','DistrictController@CheckDuplicateRegion');

});



// Request which are required for ValidOAuth token
Route::group([
    'middleware' => 'ApartmentApi\Http\Middleware\ValidOAuth',
    'prefix'     => 'v1',
    'namespace'  => 'V1'
], function()
{
    // Flat
    get('flats', 'FlatController@index');
    post('flat', 'FlatController@store');
    post('flat/attach-user', 'FlatController@attachUser');
    get('flat/{id}', 'FlatController@show');
    post('flat/{id}', 'FlatController@update');
    post('flat/{id}/delete', 'FlatController@destroy');

    // User
    get('users', 'UserController@index');

    get('blocks', 'BlockController@index');
    get('block/{id}', 'BlockController@show');

    get('buildings', 'BuildingController@index');

    // Mark payment done
    post('society/flat/bill/{id}/payment', 'FlatBillController@payment');

    //Flat Bills
    get('society/{societyId}/bills/{year}/{month}', 'FlatBillController@index');
    get('society/{societyId}/bill/report', 'FlatBillController@report');

    // Generate Flat Bills
    post('society/{societyId}/generate-bills', 'FlatBillController@generate');

    // Billing Config
    get('billing/config/{societyId}', 'BillingConfigController@index');
    post('billing/config/{societyId}', 'BillingConfigController@store');

	// Chairman Notification
	get('notification', 'NotificationController@index');
	
	get('sample/19', 'SampleController@index');
	
    // Billing item
    get('billing/items', 'ItemController@index');
    get('billing/item/{id}', 'ItemController@show');
    post('billing/item/{id}', 'ItemController@update');
    post('billing/item', 'ItemController@store');
    Route::delete('billing/item/{id}', 'ItemController@destroy');

    // Billing
    get('billings', 'BillingController@index');
    post('billing', 'BillingController@store');
    get('billing/{id}', 'BillingController@show');
    post('billing/{id}', 'BillingController@update');
    Route::delete('billing/{id}', 'BillingController@destroy');

    // Communication
    post('officialcomm/save', 'OfficialCommController@save');
    post('officialcomm/update', 'OfficialCommController@update');
    get('officialcomm/letter/to/{id}', 'OfficialCommController@getLetterTo');
    get('officialcomm/letterlist', 'OfficialCommController@getLetterList');
    get('officialcomm/letterlist/resident', 'OfficialCommController@getLetterListResident');
//    get('officialcomm/lettercount/resident', 'OfficialCommController@getLetterCountResident');
    get('officialcomm/lettercount/admin', 'OfficialCommController@getLetterCountAdmin');
    get('lettercount', 'OfficialCommController@getCount');
    get('officialcomm/letter/{id}', 'OfficialCommController@getLetter');
    post('officialcomm/letter/reply/save', 'OfficialCommController@saveReply');
    get('officialcomm/letter/reply/{id}', 'OfficialCommController@getOfficialCommReplyList');



    // Amenities
	post('amenities/update/{id}', 'AmenitiesController@updateAmenities');
    post('amenities/save', 'AmenitiesController@save');
    get('amenities/list', 'AmenitiesController@getList');
    get('amenities/amenitiescount', 'AmenitiesController@getAmenitiesCount');
    get('amenities/amenitieslist', 'AmenitiesController@getList');
    get('amenities/details/{id}', 'AmenitiesController@getDetails');
    post('amenities/delete','AmenitiesController@delete');

	// Wings Configuration :
	get('/wings/amenities', 'AmenitiesController@getAmenitiesList');


});

// Request which are not required for ValidOAuth
Route::group([
    'prefix'     => 'v1',
    'namespace'  => 'V1'
], function() {
    // Flat
    get('user/status', 'UserController@status');
});

Route::post('/parking/store/category','ParkingController@storeCategory');

// BUILDINGS AND BLOCKS
post('/building/save', 'BuildingController@createOrUpdate');
get('/building/list', 'BuildingController@buildings');
post('building/checkDuplicate', 'BuildingController@checkDuplicate');
post('building/block/checkDuplicate', 'BuildingController@checkDuplicateBlock');
get('/building/{id}', 'BuildingController@building');
get('/building/delete/{id}', 'BuildingController@deleteBuilding');
post('/building/block/save','BuildingController@createOrUpdateBlock');
get('/building/block/list/{buildingId}','BuildingController@blocks');
get('/building/block/edit','BuildingController@editBlock');
get('/building/block/delete/{blockId}','BuildingController@deleteBlock');
get('/society/buildings/{societyId}','BuildingController@societyBuildings');
post('/society/flat/check_occupancy','SocietyController@checkOccupancy');
post('/wings/flats/add', 'BuildingController@addFlats');
post('/wings/flats/update', 'BuildingController@updateFlats');
get('/wings/flats/', 'BuildingController@listFlats');
get('/wings/listAmenities', 'BuildingController@listBlockAmenities');



Route::group([
    'middleware' => 'ApartmentApi\Http\Middleware\CheckOAuthToken',
    'prefix'     => 'v1',
    'namespace'  => 'V1',
], function()
{
    get('categories','CategoryController@defaultSuperAdminTypeShow');
    get('categories/admin','CategoryController@defaultAdminTypeShow');
    post('/checkDuplicate','CategoryController@SuperadminCheckDuplicateType');
    post('admin/checkDuplicate','CategoryController@adminCheckDuplicateType');
    get('/superadmin/list/societyType','CategoryController@societyTypeList');
    get('/societyType/count','CategoryController@societyCount');
    get('/superadmin/list/typeList/{type}','CategoryController@superadminTypeList');
    get('/admin/list/typeList/','CategoryController@adminTypeList');
    get('/admin/list/typeList/{type}','CategoryController@adminTypeLists');
    get('/type/{id}','CategoryController@editType');
    post('/type/update','CategoryController@adminSaveOrUpdateType');
    post('superAdmin/type/update','CategoryController@superadminSaveOrUpdateType');
    get('/Type/delete/{id}','CategoryController@deleteType');
    get('/adminType/delete/{id}','CategoryController@admindeleteType');

    get('flat_parking/list/{id}','ParkingController@getFlatParking');

    get('vehicle_type','VehicleController@getVehicle_type');
    get('parking_category','ParkingCategoryController@getAllParkingCategories');
    post('parking_config/create','ParkingController@createParkingConfig');
    post('create/parking','ParkingController@createParkingSlots');
    get('all_slots','ParkingController@getAllSlots');
    get('parking_slots/list','ParkingController@getSlotsList');
    post('parking_config/data','ParkingController@getParkingConfig');
    post('delete/alloted_slots','ParkingController@RemoveSlot');
    get('search/slots','ParkingController@searchParkingSlots');

    // Reminders
    post('create/reminders','ReminderController@createReminder');
    post('update/reminders/{id}','ReminderController@updateReminder');
    get('category/type/list/{type}','ReminderController@MeetingTypeCategoryList');
    get('list/reminders/{type}', 'ReminderController@listReminders');
    get('reminders','ReminderController@reminder');
    get('society/reminders','ReminderController@getSocietyReminder');
    get('flat_document/reminders','ReminderController@getFlatDocumentReminder');
    get('off_comm/reminders','ReminderController@getOfficialCommReminder');
    get('flat_document/reports','ReminderController@getFlatDocumentReports');

//    Type
    post('/type/create','TypeController@store');
    get('list/type','TypeController@typeList');
    get('delete/{id}','TypeController@destroy');
    get('type/edit/{id}','TypeController@show');
    post('type/checkDuplicate','TypeController@Duplicate');


//    BuildingConfig
    post('/config/save','BuildingConfigController@store');
    get('/config/edit','BuildingConfigController@show');
    get('building/details','SocietyConfigController@listBuildingDetails');
    post('building/config/details','SocietyConfigController@BuildingConfigurationDetails');
    get('building/wings/{id}','SocietyConfigController@getBuildingWings');
    get('wing/config/detail/{id}','SocietyConfigController@getWingConfigurationDetails');
    post('building/approved','SocietyConfigController@getBuildingApprovedStatus');
    get('building/flats/{id}','SocietyConfigController@getAllBuildingFlats');
    get('wing/flats/{id}','SocietyConfigController@getAllWingFlats');
    get('building/amenities/{id}','SocietyConfigController@getBuildingAmenities');
    get('parking/{id}','SocietyConfigController@getParking');
    
    // Society Config
    get('society/config', 'SocietyConfigController@index');
    post('society/config', 'SocietyConfigController@store');
    post('society/dummy/building/{count}', 'SocietyConfigController@addDummyBuilding');

    // Society Config index
    post('import/society/config', 'Import\SocietyConfigController@index');
    get('role', 'Import\SocietyConfigController@role');
});
