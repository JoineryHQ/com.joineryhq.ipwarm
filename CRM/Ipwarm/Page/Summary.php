<?php
use CRM_Ipwarm_ExtensionUtil as E;

class CRM_Ipwarm_Page_Summary extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('IP Warming Summary'));

    $summaryList = CRM_Ipwarm_Limitmanager::getWarmupSchedule();
    $dailyUsage = CRM_Ipwarm_Limitmanager::getDailyUsage();
    $hourlyUsage = CRM_Ipwarm_Limitmanager::getHourlyUsage();
    $currentLevel = CRM_Ipwarm_Limitmanager::getCurrentLevel();

    $summaryList[$currentLevel]['class'] = 'crm-row-selected';

    $this->assign('summaryList', $summaryList);
    $this->assign('dailyUsage', $dailyUsage);
    $this->assign('hourlyUsage', $hourlyUsage);
    $this->assign('currentLevel', $currentLevel);

    parent::run();
  }

}
