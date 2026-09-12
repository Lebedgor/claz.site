<?php

namespace App\Actions;

use App\Jobs\RecordClickEvent;
use App\Models\ToolLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackLinkClick
{
    public function execute(ToolLink $link, Request $request): void
    {
        $ipHash = hash('sha256', strval($request->ip()).'|'.config('app.key'));

        RecordClickEvent::dispatch(
            $link,
            Str::limit(strval($request->header('referer')), 255),
            $ipHash,
        );
    }
}
