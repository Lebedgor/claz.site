<?php

namespace App\Jobs;

use App\Models\ClickEvent;
use App\Models\ToolLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordClickEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly ToolLink $link,
        public readonly ?string $referer,
        public readonly string $ipHash,
    ) {}

    public function handle(): void
    {
        ClickEvent::create([
            'tool_link_id' => $this->link->getKey(),
            'referer' => $this->referer !== null && $this->referer !== '' ? $this->referer : null,
            'ip_hash' => $this->ipHash,
            'created_at' => now(),
        ]);
    }
}
