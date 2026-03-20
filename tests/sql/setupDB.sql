DELETE FROM `civicrm_mail_settings` WHERE id = 1;
INSERT INTO `civicrm_mail_settings` (`id`, `domain_id`, `name`, `is_default`, `domain`, `localpart`, `return_path`, `protocol`, `server`, `port`, `username`, `password`, `is_ssl`, `source`, `activity_status`, `is_non_case_email_skipped`, `is_contact_creation_disabled_if_no_match`) VALUES (1,1,'default',1,'my.example.com',NULL,NULL,'1',NULL,NULL,NULL,NULL,0,NULL,'Completed',0,0);
INSERT INTO `civicrm_setting` (`id`, `name`, `value`, `domain_id`, `contact_id`, `is_domain`, `component_id`, `created_date`, `created_id`) VALUES (7,'navigation','s:8:\"E01r1blj\";',1,2,0,NULL,'2021-03-31 02:13:27',2);
INSERT INTO `civicrm_setting` (`id`, `name`, `value`, `domain_id`, `contact_id`, `is_domain`, `component_id`, `created_date`, `created_id`) VALUES (8,'site_id','s:32:\"e87078b64b0114581d29c9db8fc8c408\";',1,NULL,1,NULL,'2021-03-31 02:13:28',2);
INSERT INTO `civicrm_setting` (`id`, `name`, `value`, `domain_id`, `contact_id`, `is_domain`, `component_id`, `created_date`, `created_id`) VALUES (9,'site_id','s:32:\"e87078b64b0114581d29c9db8fc8c408\";',2,NULL,1,NULL,'2021-03-31 02:13:28',2);

