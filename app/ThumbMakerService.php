<?php namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ThumbMakerService {


    protected $folder;

    public function handle($payload)
    {
        $this->folder = $payload['folder'];

        if(isset($payload['bucket']))
            Config::set('S3_BUCKET', $payload['bucket']);

        $files = Storage::disk('s3')->allFiles($this->folder);

        Log::info(print_r($files, 1));


        $this->getAndMake($files);

        $this->uploadFilesBacktoS3();

    }

    protected function uploadFilesBacktoS3()
    {
        $source = base_path('storage');
        $files = File::files($source);
        foreach($files as $file)
        {
            Log::info($file);

            if(strpos($file, 'thumb_') != false)
            {
                $name = File::name($file);
                $contents = File::get($file);
                Storage::disk('s3')->put($this->folder . '/' . $name, $contents);
            }
        }
    }

    private function getAndMake($files)
    {
        foreach($files as $file) {
            if (strpos($file, 'thumb_') === false)
            {
                //Download here
                $content = Storage::disk('s3')->get($file);

                $name = File::name($file);

                $destination = base_path() . "/storage/{$name}.png";

                File::put($destination, $content);

                Log::info("Convert $file $destination");

                //Convert
                $thumb_destination = base_path() . "/storage/thumb_{$name}.gif";

                Log::info($thumb_destination);

                exec("convert -define png:size=387x500 {$destination} -auto-orient -thumbnail 387x500  -unsharp 0x.5 {$thumb_destination}", $output, $results);

                Log::info(print_r($output, 1));
            }
        }
    }
}