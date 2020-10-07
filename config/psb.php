<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Default token generation url
     |--------------------------------------------------------------------------
     |
     |the default url that the browser would open to when a user is to generate access token
     |
     */
    'TOKEN_URI' => 'https://www.example.com',
    /*
     |--------------------------------------------------------------------------
     | Default token validation url
     |--------------------------------------------------------------------------
     |
     |the default url to validate a user token
     |
     */
     'VALIDATE_TOKEN_URI' => 'https://www.example.com',
    /*
     |--------------------------------------------------------------------------
     | Default token storage file
     |--------------------------------------------------------------------------
     |
     |the default file that stores the users token
     |
     */

    'TOKEN_STORAGE' => getcwd().DIRECTORY_SEPARATOR.'token'

];
