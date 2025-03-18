<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    // Icon identifier
    'tx-aigelb-svgicon' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:aigelb/Resources/Public/Icons/Extension.svg',
    ],
];
