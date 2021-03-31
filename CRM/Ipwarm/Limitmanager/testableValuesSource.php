<?php

/**
 * Source of testable values (and values which may need to be overridden in testing).
 *
 * @author as
 */
class CRM_Ipwarm_Limitmanager_testableValuesSource {
  /**
   * @var Int The current date and time, as a Unix timestamp.
   */
  private $currentDateTime;
  
  /**
   * @var Array Current daily email usage
   */
  private $dailyUsage;

  /**
   * @var Int Current email usage in last 60 minutes
   */
  private $hourlyUsage;

  public function __construct() {
    $this->currentDateTime = time();
  }
  /**
   * Return the actual current timestamp.
   * @return Int
   */
  public function getCurrentDateTime() {
    return $this->currentDateTime;
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
   * @return Int
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
}
