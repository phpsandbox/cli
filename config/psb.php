<?php

return [
    'base_url' => $baseUrl = env('BASE_URL', 'https://phpsandbox.io'),

    /*
     |--------------------------------------------------------------------------
     | Default token generation url
     |--------------------------------------------------------------------------
     |
     |the default url that the browser would open to when a user is to generate access token
     |
     */
    'token_url' => env('TOKEN_URI','https://phpsandbox.io/login/cli'),
    /*
     |--------------------------------------------------------------------------
     | Default token validation url
     |--------------------------------------------------------------------------
     |
     |the default url to validate a user token
     |
     */
     'validate_token_url' => env('VALIDATE_TOKEN_URI','https://phpsandbox.io/auth/login/dev'),
    /*
     |--------------------------------------------------------------------------
     | Default token storage file
     |--------------------------------------------------------------------------
     |
     |the default file that stores the users token
     |
     */

    'token_storage' => implode(DIRECTORY_SEPARATOR
                                    ,[$_SERVER['HOME'] ?? __DIR__
                                    , '.phpsandbox'
                                    , 'token'
                            ]),
    /*
     |--------------------------------------------------------------------------
     | Default token storage file
     |--------------------------------------------------------------------------
     |
     |the default file that stores the users token
     |
     */

    'files_storage' => implode(DIRECTORY_SEPARATOR
            ,[$_SERVER['HOME'] ?? __DIR__
                , '.phpsandbox'
                , 'files'
            ]),
    /*
    |--------------------------------------------------------------------------
    | Retrieve authenticated user
    |--------------------------------------------------------------------------
    |
    |the default file that stores the users token
    |
    */
    'fetch_auth_user_url' => env('FETCH_AUTH_USER_URL','https://internal.phpsandbox.io/api/user'),
    /*
    |--------------------------------------------------------------------------
    | Retrieve authenticated user
    |--------------------------------------------------------------------------
    |
    |maximum file upload size
    |
    */
    'max_file_size' => env('MAX_FILE_SIZE',1000),
    /*
    |--------------------------------------------------------------------------
    | Retrieve authenticated user
    |--------------------------------------------------------------------------
    |
    |maximum file upload size
    |
    */
    'file_upload_url' => env('FILE_UPLOAD_URL','https://phpsandbox.io/api/cli/import')
];
