<?php

use Illuminate\Http\Request;

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
/* Hoem Route */
Route::get('/', 'PageController@index')->name('home');

/* Authorization Routes */
Route::get('/login', 'AuthController@index')->name('login');
Route::get('/logout', 'AuthController@logout')->name('logout');
Route::get('/callback', 'AuthController@callback')->name('callback');

Route::get('/dashboard/testing', 'MailController@testing');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard/{label_id?}', 'PageController@dashboard')->name('dashboard')->where('label_id', "[0-9]+");
    Route::get('/dashboard/fetch', 'PageController@dashboard_fetch')->name('dashboard.fetch');

    Route::get('/mail/new/{step_id}', 'PageController@new_mail_process')->name('mail.new')->where(['step_id' => "[0-9]+"]);
    Route::post('/mail/new/{step_id}',  'PageController@new_mail_process')->name('mail.new.post')->where('step_id', "[0-9]+");
    Route::get('/mail/new/reset',  'PageController@mail_reset')->name('mail.reset');
    
    Route::get('/mail/new/recipient/{recipient_id?}', 'PageController@add_recipient')->name('mail.new.recipient')->where('recipient_id', "[0-9]+");
    Route::post('/mail/new/recipient', 'PageController@add_recipient')->name('mail.new.recipient.post');
    Route::get('/mail/new', function () {
        return redirect()->route('mail.new', ['step_id' => 1]);
    });

    Route::get('/mail/{mail_id}', 'PageController@read_mail')->name('mail')->where('mail_id', "[0-9]+");
    Route::get('/mail/{mail_id}/reply/{step_id}', 'PageController@reply_mail')->name('mail.reply')->where(['step_id' => "[0-9]+", 'mail_id' => "[0-9]+"]);
    Route::get('/mail/{mail_id}/unread', 'PageController@unread_mail')->name('mail.unread')->where('mail_id', "[0-9]+");
    Route::get('/mail/{mail_id}/delete', 'PageController@delete_mail')->name('mail.delete')->where('mail_id', "[0-9]+");

    Route::get('/settings', 'PageController@settings')->name('settings');
    Route::get('/settings/update/labels', 'PageController@update_mail_labels')->name('settings.labels');
    Route::get('/settings/update/mailing_lists', 'PageController@update_mailing_lists')->name('settings.mailing_lists');

    Route::get('/welcome', 'PageController@dashboard_welcome')->name('dashboard.welcome');
    Route::get('/welcome/download', 'MailController@first_time_download')->name('dashboard.welcome.download');
});
