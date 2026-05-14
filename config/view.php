<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

    /*
    |--------------------------------------------------------------------------
    | Blade File Extension
    |--------------------------------------------------------------------------
    |
    | This option controls the file extension for compiled views. By default
    | we will use the PHP file extension so views are compiled as PHP files.
    |
    */

    'file_extension' => env('VIEW_FILE_EXTENSION', 'php'),

    /*
    |--------------------------------------------------------------------------
    | Namespaced Path Hints
    |--------------------------------------------------------------------------
    |
    | Below you may specify key => value pairs of view namespaces and their
    | associated paths. These will be registered in the view finder and let
    | you use them in your views. A leading :: will let you register the
    | namespaces as being global namespaces.
    |
    */

    'namespaces' => [
        'mail' => resource_path('views/vendor/mail'),
    ],

];
