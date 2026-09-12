<?php

namespace App\Support;

class HtmlSanitizer
{
    public static function clean(string $html): string
    {
        $serializerPath = storage_path('framework/purifier');

        if (! is_dir($serializerPath)) {
            mkdir($serializerPath, 0755, true);
        }

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,b,strong,em,i,a[href|rel],ul,ol,li,blockquote');
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('Cache.SerializerPath', $serializerPath);

        $purifier = new \HTMLPurifier($config);

        return strval($purifier->purify($html));
    }
}
