<?php

declare(strict_types=1);

namespace IGelb\Aigelb\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerService {
    public function getPidByContentUid(string $uid): mixed {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $pid = $queryBuilder
                ->select('pid')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid)),
                )
                ->executeQuery()
                ->fetchOne();

        return $pid;
    }

    public function writeChangeToPage(string $pid): void {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder
            ->update('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pid)),
            )
            ->set('tstamp', time())
            ->set('SYS_LASTCHANGED', time())
            ->executeStatement();
    }
}
