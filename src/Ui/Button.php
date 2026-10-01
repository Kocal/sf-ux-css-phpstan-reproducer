<?php

namespace App\Ui;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\UX\Css\Twig\CssRuntime;

/**
 * @phpstan-import-type CssStyles from \Symfony\UX\Css\CssReference
 */
final class Button
{
    public function __construct(
        #[Autowire(service: 'ux_css.twig.runtime')]
        private readonly CssRuntime $css,
    ) {
    }

    /**
     * @param CssStyles $styles
     */
    public function classes(array $styles): string
    {
        return $this->css->css($styles);
    }
}
