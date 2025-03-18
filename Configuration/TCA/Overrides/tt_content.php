<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

(static function (): void {
    $pluginKey = ExtensionUtility::registerPlugin(
        'aigelb',
        'Aigelbframework',
        'AI Gelb FrontEnd Plugin',
    );
})();
