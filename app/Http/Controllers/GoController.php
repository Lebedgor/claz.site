<?php

namespace App\Http\Controllers;

use App\Actions\TrackLinkClick;
use App\Models\ToolLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoController extends Controller
{
    public function __invoke(ToolLink $link, Request $request, TrackLinkClick $track): RedirectResponse
    {
        $track->execute($link, $request);

        return redirect()->away($link->url, 302);
    }
}
