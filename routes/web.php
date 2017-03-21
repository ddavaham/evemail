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
Route::get('/about-us', 'PageController@about_us')->name('about');
Route::get('/services', 'PageController@services')->name('services');
Route::get('/contact-us', 'PageController@contact_us')->name('contact');


/* Authorization Routes */
Route::get('/login', 'AuthController@index')->name('login');
Route::get('/logout', 'AuthController@logout')->name('logout');
Route::get('/callback', 'AuthController@callback')->name('callback');

Route::group(['middleware' => ['auth', 'auth.new']], function () {
    Route::get('/dashboard/{label_id?}', 'PageController@dashboard')->name('dashboard')->where('label_id', "[0-9]+");
    Route::get('/dashboard/{label_id}/multiedit', 'PageController@multiedit')->name('dashboard.multiedit')->where('label_id', "[0-9]+");
    Route::post('/dashboard/{label_id}/multiedit', 'PageController@multiedit')->name('dashboard.multiedit.post')->where('label_id', "[0-9]+");
    Route::get('/dashboard/fetch', 'PageController@dashboard_fetch')->name('dashboard.fetch');

    Route::get('/mail/new/build', 'PageController@mail_send_build')->name('mail.send.build');
    Route::post('/mail/new/build',  'PageController@mail_send_build')->name('mail.send.build.post');
    Route::get('/mail/new/preview', 'PageController@mail_send_preview')->name('mail.send.preview');
    Route::get('/mail/new/send', 'PageController@mail_send_send')->name('mail.send.send');

    Route::get('/mail/reset',  'PageController@mail_reset')->name('mail.reset');

    Route::get('/mail/new/recipient/{recipient_id?}', 'PageController@add_recipient')->name('mail.send.recipient')->where('recipient_id', "[0-9]+");
    Route::post('/mail/new/recipient', 'PageController@add_recipient')->name('mail.send.recipient.post');
    Route::get('/mail/new', function () {
        return redirect()->route('mail.send.build');
    });

    Route::get('/mail/{mail_id}', 'PageController@mail_view')->name('mail')->where('mail_id', "[0-9]+");
    Route::get('/mail/{mail_id}/reply/build', 'PageController@mail_reply_build')->name('mail.reply.build')->where('mail_id', "[0-9]+");
    Route::post('/mail/{mail_id}/reply/build', 'PageController@mail_reply_build')->name('mail.reply.build.post')->where('mail_id', "[0-9]+");
    Route::get('/mail/{mail_id}/reply/preview', 'PageController@mail_reply_preview')->name('mail.reply.preview')->where('mail_id', "[0-9]+");
    Route::get('/mail/{mail_id}/reply/send', 'PageController@mail_reply_send')->name('mail.reply.send')->where('mail_id', "[0-9]+");
    Route::get('/mail/{mail_id}/unread', 'PageController@unread_mail')->name('mail.unread')->where('mail_id', "[0-9]+");
    Route::get('/mail/{mail_id}/delete', 'PageController@delete_mail')->name('mail.delete')->where('mail_id', "[0-9]+");

    Route::get('/mail/{mail_id}/forward', 'PageController@forward_mail')->name('mail.forward')->where('mail_id', "[0-9]+");



    Route::get('/settings', 'SettingsController@overview')->name('settings');
    Route::post('/settings', 'SettingsController@overview')->name('settings.post');
    Route::get('/settings/email', 'SettingsController@email')->name('settings.email');
    Route::post('/settings/email', 'SettingsController@email')->name('settings.email.post');
    Route::get('/settings/email/{action}/{vCode?}', 'SettingsController@action')->name('settings.email.action');
    Route::get('/settings/preferences', 'SettingsController@preferences')->name('settings.preferences');
    Route::post('/settings/preferences', 'SettingsController@preferences')->name('settings.preferences.post');
    Route::get('/settings/update/labels', 'SettingsController@construction')->name('settings.labels');
    Route::get('/settings/update/mailing_lists', 'SettingsController@construction')->name('settings.mailing_lists');

    Route::get('/welcome', 'PageController@dashboard_welcome')->name('dashboard.welcome');
    Route::post('/welcome', 'PageController@dashboard_welcome')->name('dashboard.welcome.post');
});

// Route::get('/testing', 'PageController@testing');
