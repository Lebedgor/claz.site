<?php

namespace App\Enums;

enum CriterionKind: string
{
    case Score = 'score';
    case Bool = 'bool';
    case Text = 'text';
}
