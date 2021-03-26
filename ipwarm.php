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
  Civi::dispatcher()->addListener(\Civi\API\Events::RESPOND, ['CRM_Ipwarm_APIWrapper', 'RESPOND'], -100);
    \Civi::$statics[__FUNCTION__] = true;
  }
  _ipwarm_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function ipwarm_civicrm_xmlMenu(&$files) {
  _ipwarm_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function ipwarm_civicrm_postInstall() {
  _ipwarm_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function ipwarm_civicrm_uninstall() {
  _ipwarm_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function ipwarm_civicrm_enable() {
  _ipwarm_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function ipwarm_civicrm_disable() {
  _ipwarm_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function ipwarm_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _ipwarm_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function ipwarm_civicrm_managed(&$entities) {
  _ipwarm_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function ipwarm_civicrm_caseTypes(&$caseTypes) {
  _ipwarm_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function ipwarm_civicrm_angularModules(&$angularModules) {
  _ipwarm_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function ipwarm_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _ipwarm_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function ipwarm_civicrm_entityTypes(&$entityTypes) {
  _ipwarm_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function ipwarm_civicrm_themes(&$themes) {
  _ipwarm_civix_civicrm_themes($themes);
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
