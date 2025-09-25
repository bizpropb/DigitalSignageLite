<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class LinkCompatibilityWarning extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.link-compatibility-warning';
}