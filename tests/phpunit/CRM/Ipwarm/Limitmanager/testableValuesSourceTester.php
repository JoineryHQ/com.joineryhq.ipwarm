<?php

/**
 * Source of testable values (and values which may need to be overridden in testing).
 *
 * @author as
 */
class CRM_Ipwarm_Limitmanager_testableValuesSourceTester extends CRM_Ipwarm_Limitmanager_testableValuesSource {
  private $currentDateTime;
  private $dailyUsage;
  private $hourlyUsage;

  public function getCurrentDateTime() {
    return $this->currentDateTime;
  }
  public function getDailyUsage($date = NULL) {
    return $this->dailyUsage;
  }
  public function getHourlyUsage() {
    return $this->hourlyUsage;
  }
  public function setVariable($variableName, $value) {
    $this->$variableName = $value;
  }
}
