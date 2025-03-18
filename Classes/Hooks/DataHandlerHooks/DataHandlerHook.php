<?php

namespace IGelb\Aigelb\Hooks\DataHandlerHooks;

use IGelb\Aigelb\Service\DataHandlerService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerHook {
    public function processDatamap_afterDatabaseOperations(string $status, string $table, string $id, array $fieldArray, DataHandler $dataHandler): void { // @phpstan-ignore-line
        if ($table === 'tt_content' && ($status === 'update' || $status === 'new')) {
            $dataHandlerService = GeneralUtility::makeInstance(DataHandlerService::class);
            if ($status === 'new') {
                $pid = $fieldArray['pid'];
            } else {
                $pid = $dataHandlerService->getPidByContentUid($id);
            }
            if ($pid) {
                $dataHandlerService->writeChangeToPage($pid);
            }
        }
    }

    public function processCmdmap_deleteAction(string $table, string $id, array $record, bool &$recordWasDeleted, DataHandler $dataHandler): void { // @phpstan-ignore-line
        $dataHandlerService = GeneralUtility::makeInstance(DataHandlerService::class);
        $dataHandlerService->writeChangeToPage($record['pid']);
    }
}
