<?php

namespace PHPixie\Cache\Drivers\Type\File;

use PHPixie\Cache\Drivers\Type\File;

class PHP extends File
{
    protected $extension = 'php';
    protected $maxTimestamp = '9999999999';

    protected function getFileData($file)
    {
        return include $file;
    }

    protected function buildFileContents($value, $timestamp)
    {
        if($timestamp === null) {
            $timestamp = $this->maxTimestamp;
        }

        $contents = var_export($value, true);
        return "<?php /*$timestamp*/ \n return $contents;";
    }

    protected function getExpiryTimestamp($file)
    {
        $file = fopen($file, 'r');
        $time = substr(fgets($file), 8,10);
        fclose($file);

        if($time == $this->maxTimestamp) {
            return null;
        }

        return $time;
    }
}
