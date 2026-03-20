<?php

require_once 'ipwarm.civix.php';
// phpcs:disable
use CRM_Ipwarm_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function ipwarm_civicrm_config(&$config) {
  // Use a static var to ensure we don't add this listener twice, which can happen
  // when running the api with `cv`.
  if (!isset(\Civi::$statics[__FUNCTION__]) || !\Civi::$statics[__FUNCTION__]) {
    Civi::dispatcher()->addListener(\Civi\API\Events::PREPARE, ['CRM_Ipwarm_APIWrapper', 'PREPARE'], -100);
    \Civi::$statics[__FUNCTION__] = true;
  }
  _ipwarm_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function ipwarm_civicrm_install() {
  _ipwarm_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function ipwarm_civicrm_enable() {
  _ipwarm_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function ipwarm_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function ipwarm_civicrm_navigationMenu(&$menu) {
  $pages = array(
    'admin_page' => array(
      'label' => E::ts('IP Warming Summary'),
      'name' => 'IP Warming Summary',
      'url' => 'civicrm/admin/ipwarm/summary?reset=1',
      'parent' => array('Administer', 'CiviMail'),
      'permission' => 'access CiviCRM',
    ),
  );

  foreach ($pages as $page) {
    // Check that our item doesn't already exist.
    $menu_item_properties = array('url' => $page['url']);
    $existing_menu_items = array();
    CRM_Core_BAO_Navigation::retrieve($menu_item_properties, $existing_menu_items);
    if (empty($existing_menu_items)) {
      // Now we're sure it doesn't exist; add it to the menu.
      $menuPath = implode('/', $page['parent']);
      unset($page['parent']);
      _ipwarm_civix_insert_navigation_menu($menu, $menuPath, $page);
    }
  }
}
