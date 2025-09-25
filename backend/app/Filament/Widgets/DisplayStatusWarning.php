<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DisplayStatusWarning extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.display-status-warning';
}