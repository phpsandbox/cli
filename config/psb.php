<?php

return [
    'base_url' => $baseUrl = env('BASE_URL', 'https://phpsandbox.io'),

    /*
     |--------------------------------------------------------------------------
     | Default token storage file
     |--------------------------------------------------------------------------
     |
     |the default file that stores the users token
     |
     */

    'token_storage' => implode(DIRECTORY_SEPARATOR
                                    ,[$_SERVER['HOME'] ?? $_SERVER['USERPROFILE']
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
            ,[$_SERVER['HOME'] ?? $_SERVER['USERPROFILE']
                , '.phpsandbox'

            ]),
    /*
    |--------------------------------------------------------------------------
    | Retrieve authenticated user
    |--------------------------------------------------------------------------
    |
    |maximum file upload size
    |
    */
    'max_file_size' => env('MAX_FILE_SIZE',1000),
];
