<?php

namespace App\Enums;

enum ToolType: string
{
    case Plugin = 'plugin';
    case Service = 'service';
    case Ai = 'ai';
}
