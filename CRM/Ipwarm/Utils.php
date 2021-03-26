<?php

use CRM_Ipwarm_ExtensionUtil as E;

/**
 * Utility methods for ipwarm extension.
 * 
 * DEPRECATED: Please use comparable methods in CRM_Ipwarm_Limitmanager() object.
 *
 * @author as
 */
class CRM_Ipwarm_Utils {

  /**
   * DEPRECATED: Please use comparable methods in CRM_Ipwarm_Limitmanager() object.
   */
  public static function getWarmupSchedule() {
    $limitManager = new CRM_Ipwarm_Limitmanager();
    return call_user_func([$limitManager, __FUNCTION__]);
  }

  /**
   * DEPRECATED: Please use comparable methods in CRM_Ipwarm_Limitmanager() object.
   */
  public static function getDailyUsage() {
    $limitManager = new CRM_Ipwarm_Limitmanager();
    return call_user_func([$limitManager, __FUNCTION__]);
  }

  /**
   * DEPRECATED: Please use comparable methods in CRM_Ipwarm_Limitmanager() object.
   */
  public static function getHourlyUsage() {
    $limitManager = new CRM_Ipwarm_Limitmanager();
    return call_user_func([$limitManager, __FUNCTION__]);
  }

  /**
   * DEPRECATED: Please use comparable methods in CRM_Ipwarm_Limitmanager() object.
   */
  public static function getCurrentLevel() {
    $limitManager = new CRM_Ipwarm_Limitmanager();
    return call_user_func([$limitManager, __FUNCTION__]);
  }

}
