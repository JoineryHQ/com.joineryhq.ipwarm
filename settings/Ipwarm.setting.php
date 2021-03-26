<?php

use CRM_Ipwarm_ExtensionUtil as E;

return array(
  'ipwarm_level' => array(
    'group_name' => 'Ipwarm Settings',
    'group' => 'ipwarm',
    'name' => 'ipwarm_level',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'title' => E::ts('Ipwarm Level'),
    'type' => 'Int',
    'default' => 0,
    'quick_form_type' => 'Element',
    'html_type' => 'Hidden',
  ),
  'ipwarm_latest_date_level' => array(
    'group_name' => 'Ipwarm Settings',
    'group' => 'ipwarm',
    'name' => 'ipwarm_latest_date_level',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'title' => E::ts('Ipwarm Latest Date Level'),
    'type' => 'String',
    'default' => 0,
    'quick_form_type' => 'Element',
    'html_type' => 'Hidden',
  ),
  'ipwarm_paused_mailings' => array(
    'group_name' => 'Ipwarm Settings',
    'group' => 'ipwarm',
    'name' => 'ipwarm_paused_mailings',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'title' => E::ts('Ipwarm Paused Mailings'),
    'type' => 'String',
    'default' => 0,
    'quick_form_type' => 'Element',
    'html_type' => 'Hidden',
  ),
);
