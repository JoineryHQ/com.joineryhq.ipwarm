<?php

/**
 * Implements an API Wrapper to signal the membership creation preHook that
 * we're currently inside of a payment transaction.
 */
class CRM_Ipwarm_APIWrapper {

  public static function PREPARE($event) {
    $request = $event->getApiRequestSig();

    switch($request) {
      case '3.job.process_mailing':
        $limitManager = new CRM_Ipwarm_Limitmanager();
        if ($limitManager->testWasLatestDateLevelYesterday()) {
          $limitManager->setWarmingLevel();
        }
        $limitManager->limitMailings();
        break;
    }
  }
}
