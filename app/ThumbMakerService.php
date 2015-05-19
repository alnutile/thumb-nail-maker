<?php namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ThumbMakerService {


    protected $folder;
    protected $thumbnail_destination;

    public function handle($payload)
    {
        $this->folder = $payload['folder'];

        $this->cleanUp();

        if(isset($payload['bucket']))
            Config::set('S3_BUCKET', $payload['bucket']);

        if(isset($payload['region']))
            Config::set('S3_REGION', $payload['region']);

        if(isset($payload['secret']))
            Config::set('S3_SECRET', $payload['secret']);

        if(isset($payload['key']))
            Config::set('S3_KEY', $payload['key']);

        if(isset($payload['destination']))
        {
            $this->thumbnail_destination = $payload['destination'];
        } else {
            $this->thumbnail_destination = base_path("storage");
        }

        if(!Storage::exists($this->thumbnail_destination))
            Storage::makeDirectory($this->thumbnail_destination, 0755, true);

        $files = Storage::disk('s3')->allFiles($this->folder);

        Log::info(print_r($files, 1));

        $this->getAndMake($files);

        $this->uploadFilesBacktoS3();

        $this->cleanUp();
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
                $content = Storage::disk('s3')->get($file);

                $name = File::name($file);

                $destination = base_path() . "/storage/{$name}.png";

                File::put($destination, $content);

                Log::info("Convert $file $destination");

                $thumb_destination = $this->thumbnail_destination . "/thumb_{$name}.gif";

                Log::info($thumb_destination);
                if(!File::exists($thumb_destination))
                    exec("convert -define png:size=387x500 {$destination} -auto-orient -thumbnail 387x500  -unsharp 0x.5 {$thumb_destination}", $output, $results);
            }
        }
    }

    private function cleanUp()
    {
        $files = File::files(storage_path());
        foreach($files as $file)
        {
            if(strpos(File::mimeType($file), 'image') != false)
            {
                File::delete($file);
            }
        }
    }
}