<?php

/**
 * Implements an API Wrapper to signal the membership creation preHook that
 * we're currently inside of a payment transaction.
 */
class CRM_Ipwarm_APIWrapper {

  public static function RESPOND($event) {
    $request = $event->getApiRequestSig();
    $apiRequest = $event->getApiRequest();
    $result = $event->getResponse();

    switch($request) {
      case '3.job.process_mailing':
        Civi::log()->info('com.joineryhq.ipwarm: we observe that the job.process_mailing api has complete just now.');
        break;
    }
  }

}
