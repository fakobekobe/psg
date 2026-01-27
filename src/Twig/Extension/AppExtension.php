<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter(name: 'nom_classe', callable: [AppExtensionRuntime::class, 'nom_classe']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(name: 'app_modal_btn', callable: [AppExtensionRuntime::class, 'app_modal_btn']),
            new TwigFunction(name: 'app_modal_show', callable: [AppExtensionRuntime::class, 'app_modal_show']),
            new TwigFunction(name: 'puce_alphabet', callable: [AppExtensionRuntime::class, 'puce_alphabet']),
            new TwigFunction(name: 'get_contenu', callable: [AppExtensionRuntime::class, 'get_contenu']),
            new TwigFunction(name: 'app_configuration', callable: [AppExtensionRuntime::class, 'app_configuration']),
        ];
    }
}
