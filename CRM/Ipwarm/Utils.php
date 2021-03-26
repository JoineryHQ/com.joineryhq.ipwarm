<?php

use CRM_Ipwarm_ExtensionUtil as E;

/**
 * Utility methods for ipwarm extension.
 *
 * @author as
 */
class CRM_Ipwarm_Utils {

  public static function getWarmupSchedule() {
    return [
      // Age-in-days => ['hourly' => hourl-limit, 'daily' => daily-limit]
      0 => ['hourly' => 20, 'daily' => 50],
      1 => ['hourly' => 28, 'daily' => 100],
      2 => ['hourly' => 39, 'daily' => 500],
      3 => ['hourly' => 55, 'daily' => 1000],
      4 => ['hourly' => 77, 'daily' => 5000],
      5 => ['hourly' => 108, 'daily' => 10000],
      6 => ['hourly' => 151, 'daily' => 20000],
      7 => ['hourly' => 211, 'daily' => 40000],
      8 => ['hourly' => 295, 'daily' => 70000],
      9 => ['hourly' => 413, 'daily' => 100000],
      10 => ['hourly' => 579, 'daily' => 150000],
      11 => ['hourly' => 810, 'daily' => 250000],
      12 => ['hourly' => 1000, 'daily' => 400000],
      13 => ['hourly' => 1587, 'daily' => 600000],
      14 => ['hourly' => 2222, 'daily' => 1000000],
      15 => ['hourly' => 3111, 'daily' => 2000000],
      16 => ['hourly' => 4356, 'daily' => 4000000],
    ];
  }

  public static function getDailyUsage() {
    return 700;
  }

  public static function getHourlyUsage() {
    return 10;
  }

  public static function getCurrentLevel() {
    return 3;
  }

}
