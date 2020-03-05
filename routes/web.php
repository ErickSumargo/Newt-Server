<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('test', 'BaseController@estConnection');

Route::group(['prefix' => 'register'], function () {
    Route::post('phone', 'UserController@registerPhone');
    Route::post('resend_code', 'UserController@resendCode');
    Route::post('verify_code', 'UserController@verifyCode');
    Route::post('student', 'UserController@registerStudent');
});

Route::group(['prefix' => 'reset_password'], function () {
    Route::post('phone', 'PasswordController@validatePhone');
    Route::post('password', 'PasswordController@resetPassword');
});

Route::post('login', 'UserController@login');

Route::group(['middleware' => 'validate.token'], function () {
    Route::get('update', 'BaseController@updateApp');
    Route::get('features', 'BaseController@loadFeatures');

    Route::post('update_profile', 'UserController@updateProfile');
    Route::post('check_transaction', 'UserController@checkTransaction');

    Route::get('get_rating', 'UserController@getRating');
    Route::post('set_rating', 'UserController@setRating');

    Route::get('get_avg_rating', 'UserController@getAvgRating');
    Route::get('get_transaction_datas', 'UserController@getTransactionDatas');

    Route::group(['prefix' => 're_register'], function () {
        Route::post('phone', 'UserController@reregisterPhone');
        Route::post('reset_phone', 'UserController@resetPhone');
    });

    Route::group(['prefix' => 'base'], function () {
        Route::get('load_dialogs', 'BaseController@loadDialogs');
        Route::get('load_teachers', 'BaseController@loadTeachers');
        Route::get('fetch_teachers', 'BaseController@fetchTeachers');

        Route::get('fetch_profiles', 'BaseController@fetchProfiles');

        Route::get('load_private_teachers', 'BaseController@loadPrivateTeachers');
    });

    Route::group(['prefix' => 'profile'], function () {
        Route::post('apply_promo_code', 'UserController@applyPromoCode');
    });

    Route::group(['prefix' => 'chat'], function () {
        Route::post('add', 'ChatController@add');

        Route::get('load_queue', 'ChatController@loadQueue');
        Route::post('mark_queue', 'ChatController@markQueue');
    });

    Route::group(['prefix' => 'challenge'], function () {
        Route::post('register', 'UserController@registerChallenger');
        Route::get('load_questions', 'ChallengeController@loadQuestions');
        Route::get('load_question_detail', 'ChallengeController@loadQuestionDetail');
        Route::post('submit_answer', 'ChallengeController@submitAnswer');

        Route::get('load_ranks', 'ChallengeController@loadRanks');
        Route::get('load_records', 'ChallengeController@loadRecords');
        Route::get('load_histories', 'ChallengeController@loadHistories');
    });
});

Route::group(['prefix' => 'admin'], function () {
    Route::post('login', 'AdminController@login');

    Route::get('load_lessons', 'AdminController@loadLessons');
    Route::get('load_private_lessons', 'AdminController@loadPrivateLessons');

    Route::post('register_teacher', 'AdminController@registerTeacher');
    Route::post('register_private_teacher', 'AdminController@registerPrivateTeacher');

    Route::post('submit_tips', 'AdminController@submitTips');
    Route::post('submit_challenge', 'AdminController@submitChallenge');
    Route::post('activate_challenge', 'AdminController@activateChallenge');
    Route::get('load_queues_challenge', 'AdminController@loadQueuesChallenge');
    Route::post('reset_rank', 'AdminController@resetRank');
    Route::get('load_all_challenge', 'AdminController@loadChallenges');
    Route::get('load_challenge', 'AdminController@loadChallengeDetail');
    Route::post('edit_challenge', 'AdminController@editChallenge');

    Route::get('load_all_tips', 'AdminController@loadTips');
    Route::get('load_tips', 'AdminController@loadTipsDetail');
    Route::post('edit_tips', 'AdminController@editTips');
    Route::post('activate_tips', 'AdminController@activateTips');

    Route::get('send_notification', 'AdminController@sendNotification');
    Route::post('confirm_payment', 'AdminController@confirmPayment');
});