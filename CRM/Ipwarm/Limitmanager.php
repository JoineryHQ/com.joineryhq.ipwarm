<?php


/**
 * Object that manages sending limits for ipwarm extension.
 *
 * @author as
 */
class CRM_Ipwarm_Limitmanager {

  /**
   * @var Int Current daily email usage
   */
  var $dailyUsage;

  /**
   * @var Int Current email usage in last 60 minutes
   */
  var $hourlyUsage;

  /**
   * @var Int Current warming level
   */
  var $currentWarmingLevel;
  
  /**
   * The current date and time, as a Unix timestamp. Useful for testing.
   * @var Int 
   */
  var $currentDateTime;
  
  public function __construct() {
//    $this->currentDateTime = time();
    $this->currentDateTime = strtotime('+3 day');
  }

  /**
   * Return an array of attributes for each level of the warming schedule,
   * where each level is expected to be progressed through from one day to the next,
   * and where each level is a keyed array element of the format:
   * Age-in-days => ['hourly' => hourly-limit, 'daily' => daily-limit]
   *
   * @return Array
   */
  public function getWarmingSchedule() {
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
   * a given day; this is the total delivereed plus the total bounced, per
   * tables civicrm_mailing_event_bounce and civicrm_mailing_event_delivered
   *
   * @param String $date A date in the format YYYYMMDD; if NULL, the current date is used.
   *
   * @return Int
   */
  public function getDailyUsage($date = NULL) {
    if (!isset($date)) {
      $date = date("Ymd", $this->currentDateTime);
    }
    $nextDate = date('Ymd', strtotime('+1 day', strtotime($date)));
    if (!isset($this->dailyUsage[$date])) {
      $query = "
        SELECT SUM(t.cnt)
        FROM (
          SELECT 
            COUNT(*) cnt, 'bounce'
          FROM 
            civicrm_mailing_event_bounce
          WHERE
            time_stamp BETWEEN %1 AND %2
        UNION
          SELECT 
            COUNT(*), 'delivered'
          FROM 
            civicrm_mailing_event_delivered
          WHERE
            time_stamp BETWEEN %1 AND %2
        ) t
      ";
      $queryParams = [
        '1' => [$date, 'Int'],
        '2' => [$nextDate, 'Int']
      ];
      $this->dailyUsage[$date] = CRM_Core_DAO::singleValueQuery($query, $queryParams);
    }
    return $this->dailyUsage[$date];
  }

  /**
   * Get the total number of emails sent (or attempted sent) via CiviMail within
   * the last 60 minutes until now; this is the total delivereed plus the total 
   * bounced, per tables civicrm_mailing_event_bounce and civicrm_mailing_event_delivered
   *
   * @return int
   */
  public function getHourlyUsage() {
    if (!isset($this->hourlyUsage)) {
      $query = "
        SELECT SUM(t.cnt)
        FROM (
          SELECT 
            COUNT(*) cnt, 'bounce'
          FROM 
            civicrm_mailing_event_bounce
          WHERE
            -- FIXME: ADJUST FOR this->currentDateTime
            time_stamp > DATE_ADD(FROM_UNIXTIME(%1), INTERVAL -1 HOUR)
        UNION
          SELECT 
            COUNT(*), 'delivered'
          FROM 
            civicrm_mailing_event_delivered
          WHERE
            -- FIXME: ADJUST FOR this->currentDateTime
            time_stamp > DATE_ADD(FROM_UNIXTIME(%1), INTERVAL -1 HOUR)
        ) t
      ";
      $queryParams = [
        1 => [$this->currentDateTime, 'Int'],
      ];
      $this->hourlyUsage = CRM_Core_DAO::singleValueQuery($query, $queryParams);      
    }
    return $this->hourlyUsage;
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
    if($this->testWasLatestDateLevelCompleted()) {
      $this->incrementWarmingLevelSetting();
    }
    elseif ($this->testWasLatestDateUsageOverPreviousLevel()) {
      // Yesterday's usage was above the previous level's maximum, but not
      // over the threshold for completion of yesterday's level; therefore
      // we neither increment nor decrement, but simply stay at yesterday's level.
    }
    else {
      $this->decrementWarmingLevelSetting();
    }
    $this->setLatestDateAndLevel();
  }

  /**
   * Perform necessary calculations to limit the volume of outbound mailings.
   * To do this, we compare the current daily and hourly volume to the limits set
   * in the current warming level. If either limit has been reached, we pauseActiveMailings();
   * otherwise, we unpausePausedMailings(). Also, we set the Mailer Batch
   * Limit to a number equal to (the current level's hourly limit) - (current
   * hourly volume), with the exception that Mailer Batch Limit should never
   * be less than 1.
   */
  public function limitMailings() {
    $dailyUsage = $this->getDailyUsage();
    $hourlyUsage = $this->getHourlyUsage();
    $warmingSchedule = $this->getWarmingSchedule();
    $currentLevel = $this->getSetting('ipwarm_level');
    $currentLevelSchedule = $warmingSchedule[$currentLevel];
    if (
      $dailyUsage >= $currentLevelSchedule['daily']
      || $hourlyUsage >= $currentLevelSchedule['hourly']
    ) {
      $this->pauseActiveMailingJobs();
    }
    else {
      $this->unpausePausedMailingJobs();
    }

    $newBatchLimit = ($currentLevelSchedule['hourly'] - $hourlyUsage);
    if ($newBatchLimit < 1) {
      $newBatchLimit = 1;
    }
    $this->setSetting('mailerBatchLimit', $newBatchLimit);
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
    Civi::settings()->set($settingName, $settingValue);
  }

  /**
   * Test whether the daily usage met 90% of the maximum for the date and level
   * in getLatestDateAndLevel(). To do this, we get the date and level from
   * getLatestDateAndLevel(), and the daily maximum for that level from
   * getWarmingSchedule(), and the daily usage for that date from getDailyUsage().
   * We then calculate 90% of that daily maximum, and return a Boolean indicating
   * whether the daily usage exceeded that 90% value.
   *
   * @return Bool
   */
  public function testWasLatestDateLevelCompleted() {
    $latest = $this->getLatestDateAndLevel();
    $warmingSchedule = $this->getWarmingSchedule();
    $latestLevelSchedule = $warmingSchedule[$latest['level']];
    $latestDateUsage = $this->getDailyUsage($latest['date']);
    $completionUsage = ($latestLevelSchedule['daily'] * .9);
    return ($latestDateUsage >= $completionUsage);
  }

  /**
   * Referencing the date and level from getLatestDateAndLevel(), test whether the
   * daily usage for that date was over the daily maximum for the previous level.
   * E.g., if level from getLatestDateAndLevel() was 3, compare actual usage
   * to the maximum for level 2.
   *
   * To do this, we get the date and level from getLatestDateAndLevel(), and
   * decrement the level by 1 (keeping to a minimum of 0). We then get the daily
   * maximum for that decemented level from getWarmingSchedule(), and the daily
   * usage for that date from getDailyUsage(). We then return a Boolean indicating
   * whether the daily usage exceeded that daily maximum.
   *
   * @return Bool
   */
  public function testWasLatestDateUsageOverPreviousLevel() {
    $latest = $this->getLatestDateAndLevel();
    $warmingSchedule = $this->getWarmingSchedule();
    $previousLevel = ($latest['level'] - 1);
    if ($previousLevel < 0) {
      $previousLevel = 0;
    }
    $previousLevelSchedule = $warmingSchedule[$previousLevel];
    $latestDateUsage = $this->getDailyUsage($latest['date']);
    return ($latestDateUsage >= $previousLevelSchedule['daily']);
  }

  /**
   * Increment the warming level by 1. To do this, we get the current warming level,
   * and add 1 to it (keeping to a maximum value of the keys from getWarmingSchedule()).
   * We then save the new warming level in settings.
   */
  public function incrementWarmingLevelSetting() {
    $newWarmingLevel = $this->getSetting('ipwarm_level') + 1;
    $maxWarmingLevel = max(array_keys($this->getWarmingSchedule()));
    if ($newWarmingLevel > $maxWarmingLevel) {
      $newWarmingLevel = $maxWarmingLevel;
    }
    $this->setSetting('ipwarm_level', $newWarmingLevel);
  }

  /**
   * Decrement the warming level by 1. To do this, we get the current warming level,
   * and subtract 1 from it (keeping to a minimum value of the keys from
   * getWarmingSchedule()). We then save the new warming level in settings.
   */
  public function decrementWarmingLevelSetting() {
    $newWarmingLevel = $this->getSetting('ipwarm_level') - 1;
    $minWarmingLevel = min(array_keys($this->getWarmingSchedule()));
    if ($newWarmingLevel < $minWarmingLevel) {
      $newWarmingLevel = $minWarmingLevel;
    }
    $this->setSetting('ipwarm_level', $newWarmingLevel);
  }

  /**
   * Store the current date and level in a setting, in the format "Date:Level"
   *   i.e., date as YYYYMMDD, then a colon, then an integer representing a
   *   warming level.
   */
  public function setLatestDateAndLevel() {
    $date = date("Ymd", $this->currentDateTime);
    $level = $this->getSetting('ipwarm_level');
    $currentDateAndLevel = "{$date}:{$level}";
    $this->setSetting('ipwarm_latest_date_level', $currentDateAndLevel);
  }

  /**
   * Get the most recently set "current date and level" from settings. This may be leftover
   * from yesterday, for example.
   *
   * @return Array [date => YYYYMMDD, level => N] i.e., date as YYYYMMDD, and
   *   an integer representing a warming level.
   */
  public function getLatestDateAndLevel() {
    list($date, $level) = explode(':', $this->getSetting('ipwarm_latest_date_level'));
    return [
      'date' => $date,
      'level' => $level
    ];
  }

  /**
   * Referencing the current date and time, test whether getLatestDateAndLevel()
   * is for yesterday's date.
   *
   * @return Bool
   */
  public function testWasLatestDateLevelNullOrYesterday() {
    $yesterdayDate = date('Ymd', strtotime('1 day ago', $this->currentDateTime));
    $latestDateAndLevel = $this->getLatestDateAndLevel();
    return (empty($latestDateAndLevel['date']) || $yesterdayDate == $latestDateAndLevel['date']);
  }

  /**
   * Pause all active and unfinished mailings, and store ids of our paused mailings in the
   * ipwarm_paused_mailings setting.
   */
  public function pauseActiveMailingJobs() {
    // get all mailing jobs that are "active" based on status
    // Valid statuses are listed in CRM_Mailing_BAO_MailingJob::status().
    // At time of writing (Saints preserve us!) these are as follows, and
    // we're intrested in the starred ones:
    //   'Scheduled' * 
    //   'Running'   *
    //   'Complete'
    //   'Paused'
    //   'Canceled'
    $newlyPausedMaingJobIds = [];
    $mailingJobs = civicrm_api3('MailingJob', 'get', [
      'sequential' => 1,
      'status' => ['IN' => ["Running", "Scheduled"]],
    ]);
    foreach ($mailingJobs['values'] as $mailingJob) {
      civicrm_api3('MailingJob', 'create', [
        'id' => $mailingJob['id'],
        'status' => 'Paused',
      ]); 
      $newlyPausedMaingJobIds[] = $mailingJob['id'];
    }
    $this->updatePausedMailingJobs($newlyPausedMaingJobIds);
  }

  /**
   * Update the setting ipwarm_paused_mailings to include a list of newly
   * paused mailing job IDs, in addition to any already stored. To do this, we
   * getPausedMailingJobs(), remove from that list any that are 
   * already completed or canceled, append the new list of paused jobs, and 
   * filter the whole for unique values; then store that final list with
   * setPausedMailingJobs().
   * 
   * @param Array $newlyPausedMaingJobIds 
   */
  public function updatePausedMailingJobs($newlyPausedMaingJobIds) {
    $pausedMailingJobIds = $this->getPausedMailingJobIds();
    if (!empty($pausedMailingJobIds)) {
      $pausedUncompletedMaingJobs = civicrm_api3('MailingJob', 'get', [
        'sequential' => 1,
        'status' => ['NOT IN' => ["Complete", "Canceled"]],
        'id' => ['IN' => $pausedMailingJobIds],
      ]);
    }
    $pausedMailingJobIds = array_unique(array_merge($pausedMailingJobIds, $newlyPausedMaingJobIds));
    $this->setPausedMailingJobIds($pausedMailingJobIds);
  }
  
  /** 
   * Store an array of IDs for paused mailing jobs into the setting ipwarm_paused_mailings. 
   * To do this, we implode the given array with commas, and save to settings.
   */
  public function setPausedMailingJobIds($pausedMailingJobIds) {
    $settingValue = implode(',',  $pausedMailingJobIds);
    $this->setSetting('ipwarm_paused_mailings', $settingValue);
  }
  
  /** 
   * Get an array of IDs for paused mailing jobs. To do this, we get the setting
   * ipwarm_paused_mailings and split it on comma into an array.
   */
  public function getPausedMailingJobIds() {
    $settingValue = $this->getSetting('ipwarm_paused_mailings');
    $pausedMailingJobIds = array_filter(explode(',', $settingValue));
    return $pausedMailingJobIds;
  }
  /**
   * Unpause all mailings that we have paused (assuming their status is still Paused)
   * and update the safed list of paused malings to reflect zero paused mailings.
   */
  public function unpausePausedMailingJobs() {
    $pausedMailingJobIds = $this->getPausedMailingJobIds();
    if (!empty($pausedMailingJobIds)) {
      $pausedMailingJobs = civicrm_api3('MailingJob', 'get', [
        'sequential' => 1,
        'status' => 'Paused',
        'id' => ['IN' => $pausedMailingJobIds],
      ]);
      foreach ($pausedMailingJobs['values'] as $pausedMailingJob) {
        // Would rather use an api here, but the BAO -- and no api -- handles this
        // in an intelligent way, so that "resume" will change the status to 
        // Running or Scheduled appropriately. Note that the BAO is expecting 
        // a mailing.id, not a mailing_job.id.
        CRM_Mailing_BAO_MailingJob::resume($pausedMailingJob['mailing_id']);
      }
    }
    $this->setPausedMailingJobIds([]);
  }
}
