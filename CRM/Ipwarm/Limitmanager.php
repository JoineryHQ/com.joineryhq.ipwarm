<?php


/**
 * Object that manages sending limits for ipwarm extension.
 *
 * @author as
 */
class CRM_Ipwarm_Limitmanager {
  /**
   * Return an array of attributes for each level of the warmup schedule,
   * where each level is expected to be progressed through from one day to the next,
   * and where each level is a keyed array element of the format:
   * Age-in-days => ['hourly' => hourly-limit, 'daily' => daily-limit]
   *
   * @return Array
   */
  public function getWarmupSchedule() {
    // FIXME: stub.
    return [
      // Age-in-days => ['hourly' => hourly-limit, 'daily' => daily-limit]
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

  /**
   * Get the total number of emails sent (or attempted sent) via CiviMail on
   * a given day; this is the total sent plus the total bounced, per
   * civicrm_mailing_event tables.
   *
   * @param String $date A date in the format YYYYMMDD; if NULL, the current date is used.
   *
   * @return Int
   */
  public function getDailyUsage($date = NULL) {
    // FIXME: stub.
    return 700;
  }

  /**
   * Get the total number of emails sent (or attempted sent) via CiviMail within
   * the last 60 minutes until now; this is the total sent plus the total bounced, per
   * civicrm_mailing_event tables.
   *
   * @return int
   */
  public function getHourlyUsage() {
    // FIXME: stub.
    return 10;
  }

  /**
   * Get the current warming level, i.e. one of the keys
   * in the array returned by self::getWarmupSchedule().
   *
   * @return int
   */
  public function getCurrentLevel() {
    // FIXME: stub.
    return 2;
  }

  /**
   * Perform necessary calculations to set the appropriate warming level. To do
   * this, we start with the current warming level, then ask whether yesterday's
   * level was completed; if it was completed, we increment the warming level;
   * if not, we decrement the level. We also record the new level with the current
   * date in a setting, as a reference for self::testWasLatestLevelCompleted() 
   * tomorrow.
   */
  public function setWarmingLevel() {
    // FIXME: stub.
    if($this->testWasLatestLevelCompleted()) {
      self::incrementWarmingLevel();
    }
    elseif ($this->testWasLatestUsageOverPreviousLevel()) {
      // Yesterday's usage was above the previous level's maximum, therefore
      // we don't decrement.
    }
    else {
      self::decrementWarmingLevel();
    }
    self::setLatestDateAndLevel();
  }

  /**
   * Perform necessary calculations to limit the volume of outbound mailings.
   * To do this, we compare the current daily and hourly volume to the limits set
   * in the current warming level. If either limit has been exceded, we pause
   * all mailings (and keep track of which mailings we paused); otherwise, we
   * unpause all of the mailings we've paused.  Also, we set the Mailer Batch
   * Limit to a number equal to (the current level's hourly limit) - (current
   * hourly volume), with the exception that Mailer Batch Limit should never
   * be less than 1.
   */
  public function limitMailings() {
    // FIXME: stub.

  }

  /**
   * Wrapper for settings get; allows easy hard-coding of settings during development.
   * 
   * @param type $settingName
   * 
   * @return Mixed
   */
  public function getSetting($settingName) {
    return Civi::settings()->get($settingName);
  }
  
  /**
   * Wrapper for settings set; allows easy forcing of settings during development.
   * 
   * @param type $settingName
   */
  public function setSetting($settingName, $settingValue) {
    return TRUE;
  }

  /**
   * Test whether the daily usage met 90% of the maximum for the date and level 
   * in getLatestDateAndLevel(). To do this, we get the date and level from
   * getLatestDateAndLevel(), and the daily maximum for that level from 
   * getWarmupSchedule(), and the daily usage for that date from getDailyUsage().
   * We then calculate 90% of that daily maximum, and return a Boolean indicating 
   * whether the daily usage exceeded that 90% value.
   * 
   * @return Bool
   */
  public function testWasLatestLevelCompleted() {
    // FIXME: stub.
  }
  
  /**
   * Referencing the date and level from getLatestDateAndLevel(), test whether the
   * daily usage for that date was over the daily maximum for the previous level.
   * E.g., if level from getLatestDateAndLevel() was 3, compare actual usage
   * to the maximum for level 2.
   * 
   * To do this, we get the date and level from getLatestDateAndLevel(), and 
   * decrement the level by 1 (keepign to a minimum of 0). We then get the daily 
   * maximum for that decemented level from getWarmupSchedule(), and the daily 
   * usage for that date from getDailyUsage(). We then return a Boolean indicating 
   * whether the daily usage exceeded that daily maximum.
   * 
   * @return Bool
   */
  public function testWasLatestUsageOverPreviousLevel() {
    // FIXME: stub.
    return TRUE;
  }

  /**
   * Increment the warming level by 1. To do this, we get the current warming level,
   * and add 1 to it (keeping to a maximum value of the keys from getWarmupSchedule()).
   * We then save the new warming level.
   */
  public function incrementWarmingLevel() {
    // FIXME: stub.
  }

  /**
   * Decrement the warming level by 1. To do this, we get the current warming level,
   * and subtract 1 from it (keeping to a maximum value of the keys from 
   * getWarmupSchedule()). We then save the new warming level.
   */
  public function decrementWarmingLevel() {
    // FIXME: stub.
  }
  
  /** 
   * Store the current date and level in a setting, in the format "Date:Level" 
   *   i.e., date as YYYYMMDD, then a comma, then an integer representing a 
   *   warming level.
   */
  public function setLatestDateAndLevel() {
    // FIXME: stub.
  }
  
  /**
   * Get the most recently set "current date and level". This may be leftover 
   * from yesterday, for example.
   * 
   * @return Array [date => YYYYMMDD, level => N] i.e., date as YYYYMMDD, and 
   *   an integer representing a warming level.
   */
  public function getLatestDateAndLevel() {
    // FIXME: stub.
  }
  
  /**
   * Referencing the current date and time, test whether getLatestDateAndLevel() 
   * is for yesterday's date.
   * 
   * @return Bool
   */
  public function testWasLatestDateLevelYesterday() {
    // FIXME: stub.
    return TRUE;
  }
}
