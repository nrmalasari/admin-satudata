<?php

namespace App\Filament\Resources\DasboardResource\Widgets;

use Filament\Widgets\ChartWidget;

class BlogPostsOverview extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
