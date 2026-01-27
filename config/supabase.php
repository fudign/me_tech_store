<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supabase URL
    |--------------------------------------------------------------------------
    |
    | This is your Supabase project URL. You can find it in your Supabase
    | project settings under API settings.
    |
    */

    'url' => env('SUPABASE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Supabase Anonymous Key
    |--------------------------------------------------------------------------
    |
    | This is your Supabase anonymous (public) key. This key is safe to use
    | in a browser if you have enabled Row Level Security for your tables.
    |
    */

    'key' => env('SUPABASE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Supabase Service Role Key
    |--------------------------------------------------------------------------
    |
    | This is your Supabase service role key. This key has admin privileges
    | and should only be used on the server. Never expose it to the client.
    |
    */

    'service_key' => env('SUPABASE_SERVICE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Supabase Storage Bucket
    |--------------------------------------------------------------------------
    |
    | Default storage bucket name for file uploads.
    |
    */

    'storage_bucket' => env('SUPABASE_STORAGE_BUCKET', 'public'),

];
