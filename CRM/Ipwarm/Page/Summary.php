<?php
use CRM_Ipwarm_ExtensionUtil as E;

class CRM_Ipwarm_Page_Summary extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('IP Warming Summary'));

    $getSummaryList = CRM_Ipwarm_Utils::getWarmupSchedule();
    $getCurrentLevel = CRM_Ipwarm_Utils::getCurrentLevel();

    $getSummaryList[$getCurrentLevel]['class'] = 'crm-row-selected';

    $this->assign('summaryList', $getSummaryList);

    parent::run();
  }

}
