<?php

use Illuminate\Support\Facades\File;

require_once __DIR__ . '/libs/bootstrap.php';

$payload = getPayload(true);

fire($payload);

function fire($payload)
{
    try
    {
        if(File::exists(storage_path('logs/lumen.log')))
        {
            File::put(storage_path('logs/lumen.log'), '');
        }

        $handler = new \App\ThumbMakerService();
        $handler->handle($payload);

        echo file_get_contents(storage_path('logs/lumen.log'));
    }

    catch(\Exception $e)
    {
        $message = sprintf("Error with worker %s", $e->getMessage());
        echo $message;
    }

}