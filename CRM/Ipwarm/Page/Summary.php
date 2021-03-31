<?php
use CRM_Ipwarm_ExtensionUtil as E;

class CRM_Ipwarm_Page_Summary extends CRM_Core_Page {

  public function run() {
    $limitManager = new CRM_Ipwarm_Limitmanager();
    
    $action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE, 'browse');
    if($action == CRM_Core_Action::UPDATE) {
      $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, FALSE, 0);
      $limitManager->setSetting('ipwarm_level', $id);
      $limitManager->setLatestDateAndLevel();
      CRM_Core_Session::setStatus(E::ts('Warming level has been changed.'), E::ts('Setting saved'), 'success');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/ipwarm/summary', 'reset=1'));
    }
      
    CRM_Utils_System::setTitle(E::ts('IP Warming Summary'));

    $summaryList = $limitManager->getWarmingSchedule();
    $dailyUsage = $limitManager->getDailyUsage();
    $hourlyUsage = $limitManager->getHourlyUsage();
    $currentLevel = $limitManager->getSetting('ipwarm_level');

    $summaryList[$currentLevel]['class'] = 'crm-row-selected';

    $this->assign('summaryList', $summaryList);
    $this->assign('dailyUsage', $dailyUsage);
    $this->assign('hourlyUsage', $hourlyUsage);
    $this->assign('currentLevel', $currentLevel);

    parent::run();
  }

}
