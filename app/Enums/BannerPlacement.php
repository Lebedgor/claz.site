<?php

namespace App\Enums;

enum BannerPlacement: string
{
    case Header = 'header';
    case Sidebar = 'sidebar';
    case InArticle = 'in_article';
}
