<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    */
    'google' => [
'client_id' => 'YOUR_GOOGLE_CLIENT_ID',
'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',

        'redirect_uri' => 'http://localhost:8080/oauth_callback.php?provider=google',

        'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',

        'token_url' => 'https://oauth2.googleapis.com/token',

        'api_base_url' => 'https://www.googleapis.com/',

        'scopes' => [
            'openid',
            'email',
            'profile',
            'https://www.googleapis.com/auth/drive.readonly',
            'https://www.googleapis.com/auth/contacts.readonly',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dropbox OAuth
    |--------------------------------------------------------------------------
    */
    'dropbox' => [
      'client_id' => 'YOUR_DROPBOX_CLIENT_ID',
'client_secret' => 'YOUR_DROPBOX_CLIENT_SECRET',

        'redirect_uri' => 'http://localhost:8080/oauth_callback.php?provider=dropbox',

        'auth_url' => 'https://www.dropbox.com/oauth2/authorize',

        'token_url' => 'https://api.dropboxapi.com/oauth2/token',

        'api_base_url' => 'https://api.dropboxapi.com/2/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Microsoft OneDrive OAuth
    |--------------------------------------------------------------------------
    */
    'onedrive' => [
        'client_id' => 'YOUR_MICROSOFT_CLIENT_ID',

        'client_secret' => 'YOUR_MICROSOFT_CLIENT_SECRET',

        'redirect_uri' => 'http://localhost:8080/oauth_callback.php?provider=onedrive',

        'auth_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',

        'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',

        'api_base_url' => 'https://graph.microsoft.com/v1.0/',

        'scopes' => [
            'offline_access',
            'openid',
            'email',
            'profile',
            'Files.Read',
            'User.Read',
        ],
    ],

];
