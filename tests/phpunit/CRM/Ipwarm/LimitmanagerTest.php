<?php

use CRM_Ipwarm_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Ipwarm_LimitmanagerTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3TestTrait;
  use \Civi\Test\ContactTestTrait;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->sqlFile(__DIR__ . '/../../../sql/setupDB.sql')
      ->sqlFile(__DIR__ . '/../../../sql/createMailingAndContacts.sql')
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * @group headless
   */
  public function testMailingCreated() {
    $this->callAPISuccessGetCount('Mailing', [], 1);
  }
  
  /**
   * @group headless
   */
  public function testLimitManagerHonorsTestingFakeTime() {
    // Initialize faker with fake values.
    $fakeTime = '100000001';
    $fakeDailyUsage = '200000000';
    $fakeHourlyUsage = '300000000';
    $this->setupFakeValueSource($fakeTime, $fakeDailyUsage, $fakeHourlyUsage);
    
    $limitManager = new CRM_Ipwarm_Limitmanager();
    $this->assertEquals($fakeTime, $limitManager->currentDateTime);
    $this->assertEquals($fakeDailyUsage, $limitManager->getDailyUsage());
    $this->assertEquals($fakeHourlyUsage, $limitManager->getHourlyUsage());
  }

  /**
   * @group headless
   */
  public function testLevel0MailingLimitIsEnforcedInFirstHour() {
    $level = 0;
    // Initialize faker with current datetime
    $this->setupFakeValueSource();
    // Ensure we're on Level 1
    $limitManager = new CRM_Ipwarm_Limitmanager();
    $limitManager->setSetting('ipwarm_level', $level);
    $limitManager->setLatestDateAndLevel();
    // Get the Level 0 limits.
    $schedule = $limitManager->getWarmingSchedule();
    $limit = min([
      $schedule[$level]['hourly'],
      $schedule[$level]['daily'],
    ]);
    // Ensure we have a new mailing, and send it out.
    $this->cloneNewMailing();
    $processMailingResult = civicrm_api3('job', 'process_mailing');
    $processedCount = $processMailingResult['values']['processed'];
    
    $this->assertEquals($limit, $processedCount);
  }
  
  /**
   * @group headless
   */
  public function testLevel1MailingLimitIsEnforcedInFirstHour() {
    $level = 1;
    // Initialize faker with current datetime
    $this->setupFakeValueSource();
    // Ensure we're on Level 1
    $limitManager = new CRM_Ipwarm_Limitmanager();
    $limitManager->setSetting('ipwarm_level', $level);
    $limitManager->setLatestDateAndLevel();
    // Get the Level 0 limits.
    $schedule = $limitManager->getWarmingSchedule();
    $limit = min([
      $schedule[$level]['hourly'],
      $schedule[$level]['daily'],
    ]);
    // Ensure we have a new mailing, and send it out.
    $this->cloneNewMailing();
    $processMailingResult = civicrm_api3('job', 'process_mailing');
    $processedCount = $processMailingResult['values']['processed'];
    
    $this->assertEquals($limit, $processedCount);
  }
  
  /**
   * @group headless
   */
  public function testLevel4MailingLimitIsEnforcedInFirstHour() {
    $level = 4;
    // Initialize faker with current datetime
    $this->setupFakeValueSource();
    // Ensure we're on Level 1
    $limitManager = new CRM_Ipwarm_Limitmanager();
    $limitManager->setSetting('ipwarm_level', $level);
    $limitManager->setLatestDateAndLevel();
    // Get the Level 0 limits.
    $schedule = $limitManager->getWarmingSchedule();
    $limit = min([
      $schedule[$level]['hourly'],
      $schedule[$level]['daily'],
    ]);
    // Ensure we have a new mailing, and send it out.
    $this->cloneNewMailing();
    $this->cloneNewMailing();
    $this->cloneNewMailing();
    $processMailingResult = civicrm_api3('job', 'process_mailing');
    $processedCount = $processMailingResult['values']['processed'];
    
    $this->assertEquals($limit, $processedCount);
  }
  
  /**
   * @group headless
   */
  public function testLevelIsAdustedOnNextDay() {
    // Set fake current time as tomorrow.
    $this->setupFakeValueSource(strtotime('+1 day') + 100);
    
    // Assert we're starting with Today as the latest date and level.
    $limitManager = new CRM_Ipwarm_Limitmanager();
    $latest = $limitManager->getLatestDateAndLevel();
    $this->assertEquals($latest['date'], date('Ymd'));
    // Call the mailing api.
    $processMailingResult = civicrm_api3('job', 'process_mailing');
    // Assert Tomorrow is now the latest date and level.
    $latest = $limitManager->getLatestDateAndLevel();
    $this->assertEquals($latest['date'], date('Ymd', strtotime('+1 day')));
  }
  
  public function cloneNewMailing() {
    $clonedMailing = civicrm_api3('Mailing', 'clone', [
      'id' => 4,
    ]);
    $clonedMailingId = $clonedMailing['id'];
    civicrm_api3('Mailing', 'create', [
      'id' => $clonedMailingId,
      'scheduled_date' => '2001-01-01 00:00:00'
    ]);
    return $clonedMailingId;
  }

  public function setupFakeValueSource($currentDateTime = NULL, $dailyUsage = 0, $hourlyUsage = 0) {
    if (!isset($currentDateTime)) {
      $currentDateTime = time();
    }
    
    global $ipwarmTestableValueSource;
    if (!isset($ipwarmTestableValueSource)) {
      $ipwarmTestableValueSource = new CRM_Ipwarm_Limitmanager_testableValuesSourceTester();
    }
    $ipwarmTestableValueSource->setVariable('currentDateTime', $currentDateTime);
    $ipwarmTestableValueSource->setVariable('dailyUsage', $dailyUsage);
    $ipwarmTestableValueSource->setVariable('hourlyUsage', $hourlyUsage);
  }
}
