<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_PRI_SECRET'),
    ],
    'eve' => [
        'client_id' => env('EVE_CLIENT_ID'),
        'client_secret' => env('EVE_CLIENT_SECRET'),
        'callback_url' => env('EVE_CALLBACK_URL'),
        'client_scopes' => "esi-mail.read_mail.v1 esi-mail.organize_mail.v1 esi-mail.send_mail.v1",
        //esi-characters.read_contacts.v1 esi-characters.write_contacts.v1 esi-ui.open_window.v1
        'oauth_url' => "https://login.eveonline.com",
        'esi_url' => "https://esi.tech.ccp.is",
        'img_serv' => "https://imageserver.eveonline.com",
        'user_agent' => "EVEMail.Space || David Davaham (David Douglas) || ddouglas@douglaswebdev.net",
        'evemail_admin_char_id' => env('EVEMAIL_ADMIN_CHAR_ID')
    ]

];
