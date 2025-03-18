<?php

declare(strict_types=1);

namespace IGelb\Aigelb\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Questions extends AbstractEntity {
    protected string $question = '';
}
