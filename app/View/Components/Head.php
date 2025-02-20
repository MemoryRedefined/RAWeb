<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Head extends Component
{
    public function __construct(
        public ?string $pageTitle = null,
        public ?string $pageDescription = null,
        public ?string $pageImage = null,
        public ?string $permalink = null,
        public ?string $pageType = null,
        public ?string $canonicalUrl = null,
    ) {
    }

    public function render(): View
    {
        return view('layouts.components.head');
    }
}
