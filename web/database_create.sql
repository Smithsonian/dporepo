DROP TABLE IF EXISTS `authoring_item`;
CREATE TABLE `authoring_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `json` json NOT NULL,
  `model_uuid` varchar(32) NOT NULL DEFAULT '',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `authoring_presentation`;
CREATE TABLE `authoring_presentation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `json` json NOT NULL,
  `model_uuid` varchar(32) NOT NULL DEFAULT '',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `background_removal_method`;
CREATE TABLE `background_removal_method` (
  `background_removal_method_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`background_removal_method_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores background_removal_methods metadata';

DROP TABLE IF EXISTS `backup`;
CREATE TABLE `backup` (
  `backup_id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_filename` varchar(2000) NOT NULL,
  `result` tinyint(1) NOT NULL,
  `error` varchar(8000) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  PRIMARY KEY (`backup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `calibration_object_type`;
CREATE TABLE `calibration_object_type` (
  `calibration_object_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`calibration_object_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores calibration_object_types metadata';

DROP TABLE IF EXISTS `camera_cluster_type`;
CREATE TABLE `camera_cluster_type` (
  `camera_cluster_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`camera_cluster_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores camera_cluster_types metadata';

DROP TABLE IF EXISTS `capture_data_element`;
CREATE TABLE `capture_data_element` (
  `capture_data_element_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `capture_dataset_id` int(11) NOT NULL,
  `capture_device_configuration_id` varchar(255) DEFAULT '',
  `capture_device_field_id` int(11) DEFAULT NULL,
  `capture_sequence_number` int(11) DEFAULT NULL,
  `cluster_position_field_id` int(11) DEFAULT NULL,
  `position_in_cluster_field_id` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_data_element_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`),
  KEY `dataset_element_guid` (`capture_device_configuration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores dataset metadata';

DROP TABLE IF EXISTS `capture_data_file`;
CREATE TABLE `capture_data_file` (
  `capture_data_file_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `capture_data_element_id` int(11) NOT NULL,
  `file_upload_id` int(11) NOT NULL,
  `capture_data_file_name` varchar(255) DEFAULT NULL,
  `capture_data_file_type` varchar(255) DEFAULT NULL,
  `variant_type` varchar(50) DEFAULT NULL,
  `is_compressed_multiple_files` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_data_file_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_data_file metadata';

DROP TABLE IF EXISTS `capture_data_file_derivative`;
CREATE TABLE `capture_data_file_derivative` (
  `capture_data_file_derivative_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `capture_data_file_id` int(11) NOT NULL,
  `file_upload_id` int(11) NOT NULL,
  `derivative_file_name` varchar(255) DEFAULT NULL,
  `derivative_file_type` varchar(255) DEFAULT NULL,
  `image_width` int(11) NOT NULL DEFAULT '0',
  `image_height` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_data_file_derivative_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_data_file_derivative metadata';

DROP TABLE IF EXISTS `capture_dataset`;
CREATE TABLE `capture_dataset` (
  `capture_dataset_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `capture_dataset_guid` varchar(255) NOT NULL DEFAULT '',
  `item_id` int(11) NOT NULL,
  `capture_dataset_field_id` int(11) NOT NULL,
  `capture_method` int(11) DEFAULT NULL,
  `capture_dataset_type` int(11) DEFAULT NULL,
  `capture_dataset_name` varchar(255) NOT NULL DEFAULT '',
  `collected_by` varchar(255) NOT NULL DEFAULT '',
  `date_of_capture` datetime NOT NULL,
  `capture_dataset_description` text,
  `collection_notes` text,
  `support_equipment` varchar(255) DEFAULT NULL,
  `item_position_type` int(11) DEFAULT NULL,
  `item_position_field_id` int(11) NOT NULL,
  `item_arrangement_field_id` int(11) NOT NULL,
  `positionally_matched_capture_datasets` varchar(255) DEFAULT '',
  `focus_type` int(11) DEFAULT NULL,
  `light_source_type` int(11) DEFAULT NULL,
  `background_removal_method` int(11) DEFAULT NULL,
  `cluster_type` int(11) DEFAULT NULL,
  `cluster_geometry_field_id` int(11) DEFAULT NULL,
  `resource_capture_datasets` varchar(255) DEFAULT '',
  `calibration_object_used` varchar(255) DEFAULT '',
  `directory_path` varchar(8000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_access_uv_map_size_id` int(11) DEFAULT NULL,
  `api_access_model_face_count_id` int(11) DEFAULT NULL,
  `api_published` int(11) DEFAULT NULL,
  `api_discoverable` int(11) DEFAULT NULL,
  `file_path` varchar(8000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_checksum` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `workflow_id` int(11) DEFAULT NULL,
  `workflow_processing_step` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `workflow_status` varchar(100) DEFAULT NULL,
  `workflow_status_detail` varchar(1000) DEFAULT NULL,
  `file_package_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`capture_dataset_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores dataset metadata';

DROP TABLE IF EXISTS `capture_dataset_match`;
CREATE TABLE `capture_dataset_match` (
  `capture_dataset_match_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_dataset_id` int(11) NOT NULL,
  `match_capture_dataset_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_dataset_match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `capture_dataset_model`;
CREATE TABLE `capture_dataset_model` (
  `capture_dataset_model_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `capture_dataset_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_dataset_model_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `capture_dataset_model_purpose`;
CREATE TABLE `capture_dataset_model_purpose` (
  `capture_dataset_model_purpose_id` int(11) NOT NULL AUTO_INCREMENT,
  `model_purpose_id` int(11) NOT NULL,
  `capture_dataset_id` int(11) NOT NULL,
  `api_access` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_dataset_model_purpose_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `capture_dataset_rights`;
CREATE TABLE `capture_dataset_rights` (
  `capture_dataset_rights_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_dataset_id` int(11) NOT NULL,
  `data_rights_restriction` varchar(255) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_dataset_rights_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_dataset_rights metadata';

DROP TABLE IF EXISTS `capture_device`;
CREATE TABLE `capture_device` (
  `capture_device_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_data_element_id` int(11) NOT NULL,
  `capture_device_component_ids` varchar(255) DEFAULT NULL,
  `calibration_file` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_device_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_device metadata';

DROP TABLE IF EXISTS `capture_device_component`;
CREATE TABLE `capture_device_component` (
  `capture_device_component_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_device_id` int(11) NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `capture_device_component_type` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `model_name` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_device_component_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_device_component metadata';

DROP TABLE IF EXISTS `capture_method`;
CREATE TABLE `capture_method` (
  `capture_method_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_method_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_methods metadata';

DROP TABLE IF EXISTS `data_rights_restriction_type`;
CREATE TABLE `data_rights_restriction_type` (
  `data_rights_restriction_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`data_rights_restriction_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores data_rights_restriction_types metadata';

DROP TABLE IF EXISTS `dataset_type`;
CREATE TABLE `dataset_type` (
  `dataset_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`dataset_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores dataset_type metadata';

DROP TABLE IF EXISTS `favorite`;
CREATE TABLE `favorite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fos_user_id` int(11) NOT NULL,
  `path` text NOT NULL,
  `page_title` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `file_package`;
CREATE TABLE `file_package` (
  `file_package_id` int(11) NOT NULL AUTO_INCREMENT,
  `package_path` varchar(4000) COLLATE utf8_unicode_ci NOT NULL,
  `manifest_contents` mediumint(9) DEFAULT NULL,
  `tagmanifest_contents` varchar(8000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tagmanifest_hash` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`file_package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `file_upload`;
CREATE TABLE `file_upload` (
  `file_upload_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `parent_record_id` int(11) DEFAULT NULL,
  `parent_record_type` varchar(100) COLLATE utf8_unicode_ci DEFAULT '',
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `file_size` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_hash` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `metadata` varchar(8000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`file_upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `focus_type`;
CREATE TABLE `focus_type` (
  `focus_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`focus_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores focus_types metadata';

DROP TABLE IF EXISTS `fos_user`;
CREATE TABLE `fos_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `email_canonical` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `confirmation_token` varchar(180) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `dn` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_957A647992FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_957A6479A0D96FBF` (`email_canonical`),
  UNIQUE KEY `UNIQ_957A6479C05FB297` (`confirmation_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `isni_data`;
CREATE TABLE `isni_data` (
  `isni_id` varchar(255) NOT NULL DEFAULT '',
  `isni_label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`isni_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores ISNI metadata';

DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `item_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `local_item_id` varchar(255) DEFAULT '',
  `item_guid` varchar(255) DEFAULT '',
  `item_description` mediumtext,
  `item_type` varchar(255) DEFAULT NULL,
  `api_access_uv_map_size_id` int(11) DEFAULT NULL,
  `api_access_model_face_count_id` int(11) DEFAULT NULL,
  `api_published` int(11) DEFAULT NULL,
  `api_discoverable` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`item_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores item metadata';

DROP TABLE IF EXISTS `item_model_purpose`;
CREATE TABLE `item_model_purpose` (
  `item_model_purpose_id` int(11) NOT NULL AUTO_INCREMENT,
  `model_purpose_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `api_access` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`item_model_purpose_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `item_position_type`;
CREATE TABLE `item_position_type` (
  `item_position_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `label_alias` varchar(255) NOT NULL DEFAULT '',
  `label_alias_` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`item_position_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores item_position_types metadata';

DROP TABLE IF EXISTS `item_type`;
CREATE TABLE `item_type` (
  `item_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`item_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores subject_types metadata';

DROP TABLE IF EXISTS `job`;
CREATE TABLE `job` (
  `job_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid_` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `uuid` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `project_id` int(11) NOT NULL,
  `job_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `job_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `job_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_completed` timestamp NULL DEFAULT NULL,
  `qa_required` int(11) NOT NULL DEFAULT '0',
  `qa_approved_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `job_import_record`;
CREATE TABLE `job_import_record` (
  `job_import_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `job_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `record_table` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(800) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  PRIMARY KEY (`job_import_record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `job_log`;
CREATE TABLE `job_log` (
  `job_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `job_log_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `job_log_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `job_log_description` varchar(800) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`job_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `light_source_type`;
CREATE TABLE `light_source_type` (
  `light_source_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`light_source_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores light_source_types metadata';

DROP TABLE IF EXISTS `model`;
CREATE TABLE `model` (
  `model_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `capture_dataset_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `model_guid` varchar(255) DEFAULT NULL,
  `date_of_creation` datetime DEFAULT NULL,
  `model_file_type` varchar(255) DEFAULT NULL,
  `derived_from` varchar(255) DEFAULT NULL,
  `creation_method` varchar(255) DEFAULT NULL,
  `model_modality` varchar(255) DEFAULT NULL,
  `units` varchar(255) DEFAULT NULL,
  `is_watertight` tinyint(1) DEFAULT NULL,
  `model_purpose` varchar(255) DEFAULT NULL,
  `model_purpose_id` int(11) DEFAULT NULL,
  `point_count` varchar(255) DEFAULT NULL,
  `has_normals` tinyint(1) NOT NULL DEFAULT '0',
  `face_count` varchar(255) DEFAULT NULL,
  `model_face_count_id` int(11) DEFAULT NULL,
  `vertices_count` varchar(255) DEFAULT NULL,
  `has_vertex_color` tinyint(1) DEFAULT NULL,
  `has_uv_space` tinyint(1) DEFAULT NULL,
  `model_maps` varchar(255) DEFAULT NULL,
  `api_published` int(11) DEFAULT NULL,
  `api_discoverable` int(11) DEFAULT NULL,
  `file_path` varchar(8000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_checksum` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `workflow_id` int(11) DEFAULT NULL,
  `workflow_processing_step` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `workflow_status` varchar(100) DEFAULT NULL,
  `workflow_status_detail` varchar(1000) DEFAULT NULL,
  `file_package_id` int(11) DEFAULT NULL,
  `parent_model_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`model_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores model metadata';

DROP TABLE IF EXISTS `model_face_count`;
CREATE TABLE `model_face_count` (
  `model_face_count_id` int(11) NOT NULL AUTO_INCREMENT,
  `model_face_count` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`model_face_count_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `model_file`;
CREATE TABLE `model_file` (
  `model_file_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `file_upload_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`model_file_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores model_file metadata';

DROP TABLE IF EXISTS `model_purpose`;
CREATE TABLE `model_purpose` (
  `model_purpose_id` int(11) NOT NULL AUTO_INCREMENT,
  `model_purpose` varchar(100) NOT NULL,
  `model_purpose_description` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`model_purpose_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `permission`;
CREATE TABLE `permission` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permission_detail` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permission_group` varchar(80) COLLATE utf8_unicode_ci DEFAULT 'general',
  `route_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`permission_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `photogrammetry_scale_bar`;
CREATE TABLE `photogrammetry_scale_bar` (
  `photogrammetry_scale_bar_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_dataset_id` int(11) NOT NULL,
  `scale_bar_id` varchar(255) DEFAULT NULL,
  `scale_bar_manufacturer` varchar(255) DEFAULT NULL,
  `scale_bar_barcode_type` varchar(255) DEFAULT NULL,
  `scale_bar_target_pairs` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`photogrammetry_scale_bar_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores photogrammetry_scale_bar metadata';

DROP TABLE IF EXISTS `photogrammetry_scale_bar_target_pair`;
CREATE TABLE `photogrammetry_scale_bar_target_pair` (
  `photogrammetry_scale_bar_target_pair_id` int(11) NOT NULL AUTO_INCREMENT,
  `photogrammetry_scale_bar_id` int(11) DEFAULT NULL,
  `target_type` varchar(255) DEFAULT NULL,
  `target_pair_1_of_2` varchar(255) DEFAULT NULL,
  `target_pair_2_of_2` varchar(255) DEFAULT NULL,
  `distance` varchar(255) DEFAULT NULL,
  `units` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`photogrammetry_scale_bar_target_pair_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores photogrammetry_scale_bar_target_pair metadata';

DROP TABLE IF EXISTS `processing_action`;
CREATE TABLE `processing_action` (
  `processing_action_id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) DEFAULT NULL,
  `preceding_processing_action_id` int(11) DEFAULT NULL,
  `date_of_action` datetime DEFAULT NULL,
  `action_method` varchar(255) DEFAULT NULL,
  `software_used` varchar(255) DEFAULT NULL,
  `action_description` mediumtext,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`processing_action_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores processing_action metadata';

DROP TABLE IF EXISTS `processing_job`;
CREATE TABLE `processing_job` (
  `processing_job_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_record_id` int(11) DEFAULT NULL,
  `parent_record_type` varchar(30) DEFAULT '',
  `ingest_job_uuid` varchar(50) NOT NULL DEFAULT '',
  `processing_service_job_id` varchar(50) NOT NULL DEFAULT '',
  `recipe` varchar(50) NOT NULL DEFAULT '',
  `job_json` text NOT NULL,
  `state` varchar(20) DEFAULT NULL,
  `asset_path` varchar(300) NOT NULL DEFAULT '',
  `date_created` datetime DEFAULT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  PRIMARY KEY (`processing_job_id`),
  KEY `job_id` (`processing_service_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `processing_job_file`;
CREATE TABLE `processing_job_file` (
  `processing_job_assets_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` varchar(50) NOT NULL DEFAULT '',
  `file_name` varchar(255) DEFAULT NULL,
  `file_contents` text,
  `date_created` datetime DEFAULT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  PRIMARY KEY (`processing_job_assets_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_stakeholder_id` int(11) DEFAULT NULL,
  `project_name` varchar(255) DEFAULT '',
  `project_description` text,
  `api_published` int(11) DEFAULT NULL,
  `api_discoverable` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `stakeholder_guid` varchar(255) DEFAULT '',
  PRIMARY KEY (`project_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`),
  KEY `projects_label` (`project_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores project metadata';

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `rolename_canonical` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `rolename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role_description` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`role_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `role_permission`;
CREATE TABLE `role_permission` (
  `role_permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`role_permission_id`),
  KEY `role_id` (`role_id`),
  KEY `permission_id` (`permission_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `scale_bar_barcode_type`;
CREATE TABLE `scale_bar_barcode_type` (
  `scale_bar_barcode_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`scale_bar_barcode_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores scale_bar_barcode_types metadata';

DROP TABLE IF EXISTS `status_type`;
CREATE TABLE `status_type` (
  `status_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`status_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores status_type metadata';

DROP TABLE IF EXISTS `subject`;
CREATE TABLE `subject` (
  `subject_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `local_subject_id` varchar(255) DEFAULT '',
  `subject_guid` varchar(255) DEFAULT '',
  `subject_name` varchar(255) DEFAULT '',
  `subject_display_name` varchar(255) DEFAULT NULL,
  `holding_entity_name` varchar(255) DEFAULT '',
  `holding_entity_local_id` varchar(255) DEFAULT NULL,
  `holding_entity_guid` varchar(255) DEFAULT '',
  `api_access_uv_map_size_id` int(11) DEFAULT NULL,
  `api_access_model_face_count_id` int(11) DEFAULT NULL,
  `date_created` varchar(255) NOT NULL DEFAULT '',
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`subject_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`),
  KEY `projects_id` (`subject_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores subject metadata';

DROP TABLE IF EXISTS `subject_model_purpose`;
CREATE TABLE `subject_model_purpose` (
  `subject_model_purpose_id` int(11) NOT NULL AUTO_INCREMENT,
  `model_purpose_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `api_access` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`subject_model_purpose_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `subject_type`;
CREATE TABLE `subject_type` (
  `subject_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`subject_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores subject_types metadata';

DROP TABLE IF EXISTS `target_type`;
CREATE TABLE `target_type` (
  `target_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`target_type_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores target_types metadata';

DROP TABLE IF EXISTS `unit`;
CREATE TABLE `unit` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`unit_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores unit metadata';

DROP TABLE IF EXISTS `unit_stakeholder`;
CREATE TABLE `unit_stakeholder` (
  `unit_stakeholder_id` int(11) NOT NULL AUTO_INCREMENT,
  `isni_id` varchar(255) DEFAULT NULL,
  `unit_stakeholder_label` varchar(255) NOT NULL DEFAULT '',
  `unit_stakeholder_label_aliases` text,
  `unit_stakeholder_full_name` varchar(255) NOT NULL DEFAULT '',
  `unit_stakeholder_guid` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`unit_stakeholder_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores unit_stakeholder metadata';

DROP TABLE IF EXISTS `user_detail`;
CREATE TABLE `user_detail` (
  `user_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `username_canonical` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `user_type` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unit',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_detail_id`),
  KEY `unit_id` (`unit_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `user_role_id` int(11) NOT NULL AUTO_INCREMENT,
  `username_canonical` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `stakeholder_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_role_id`),
  KEY `role_id` (`role_id`),
  KEY `project_id` (`project_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `uv_map`;
CREATE TABLE `uv_map` (
  `uv_map_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `capture_dataset_id` int(11) DEFAULT NULL,
  `file_upload_id` int(11) DEFAULT NULL,
  `map_type` varchar(255) DEFAULT NULL,
  `map_file_type` varchar(255) DEFAULT NULL,
  `map_size` varchar(255) DEFAULT NULL,
  `uv_map_size_id` int(11) DEFAULT NULL,
  `file_path` varchar(8000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_checksum` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `file_package_id` int(11) DEFAULT NULL,
  `model_file_repository_id` int(11) DEFAULT NULL,
  `model_file_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`uv_map_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores uv_map metadata';

DROP TABLE IF EXISTS `uv_map_size`;
CREATE TABLE `uv_map_size` (
  `uv_map_size_id` int(11) NOT NULL AUTO_INCREMENT,
  `uv_map_size` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`uv_map_size_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `workflow`;
CREATE TABLE `workflow` (
  `workflow_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `workflow_recipe_name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `workflow_definition` varchar(8000) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'The JSON describing this workflow.',
  `ingest_job_uuid` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'The ID of the relevant project or item.',
  `item_id` int(11) DEFAULT NULL,
  `step_id` varchar(200) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'A string uniquely identifying the current step within the workflow_definition JSON.',
  `step_state` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Current state of current step. Null, created, processing, done, error, failed etc.',
  `step_type` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT 'auto' COMMENT 'auto or manual',
  `processing_job_id` varchar(2000) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Cook or other processing service job_id, or null.',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  PRIMARY KEY (`workflow_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `workflow_log`;
CREATE TABLE `workflow_log` (
  `workflow_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL,
  `workflow_recipe_name` varchar(255) NOT NULL,
  `step_id` varchar(200) NOT NULL COMMENT 'A string uniquely identifying the current step within the workflow_definition JSON.',
  `step_state` varchar(200) DEFAULT NULL COMMENT 'Current state of current step. Null, created, processing, done, error, failed etc.',
  `step_type` varchar(20) NOT NULL DEFAULT 'auto' COMMENT 'auto or manual',
  `ingest_job_uuid` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'The ID of the relevant project or item.',
  `processing_job_id` varchar(2000) DEFAULT NULL COMMENT 'Cook or other processing service job_id, or null.',
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`workflow_log_id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `background_removal_method` VALUES (1,'none','2017-10-23 02:00:55',2,'2018-02-10 18:27:53',1,1),(2,'clip_white','2017-10-23 02:01:18',2,'2018-07-10 19:05:10',5,1),(3,'clip_black','2017-10-23 02:01:24',2,'2018-07-10 19:05:14',2,1),(4,'background_subtraction_by_image_set','2017-10-23 02:01:35',2,'2018-07-10 19:05:36',2,1);
INSERT INTO `calibration_object_type` VALUES (1,'scale_bar','2017-10-23 16:01:47',2,'2018-07-10 19:05:42',2,1),(2,'gray_card','2017-10-23 16:01:54',2,'2018-07-10 19:05:44',2,1),(3,'unknown','2017-10-23 16:02:01',2,'2018-04-04 14:04:12',1,1);
INSERT INTO `camera_cluster_type` VALUES (1,'none','2017-10-23 02:04:06',2,'2018-04-04 13:59:11',5,1),(2,'array','2017-10-23 02:04:12',2,'2018-04-04 13:59:11',5,1),(3,'spherical_image_station','2017-10-23 02:04:20',2,'2018-07-10 19:05:53',1,1),(4,'focal_stack_position_based','2017-10-23 02:04:28',2,'2018-07-10 19:06:02',5,1),(13,'focal_stack_focus_based','2018-04-02 10:18:54',1,'2018-07-10 19:06:11',1,1);
INSERT INTO `data_rights_restriction_type` VALUES (1,'none','2017-10-23 15:49:48',2,'2018-02-10 18:44:00',1,1),(2,'copyrighted','2017-10-23 15:50:07',2,'2017-10-23 19:50:07',2,1),(3,'culturally_sensitive','2017-10-23 15:50:15',2,'2018-07-10 19:06:25',2,1),(4,'si_terms_of_use','2017-10-23 15:50:26',2,'2018-07-10 19:06:34',1,1),(5,'embargo','2017-10-23 15:50:33',2,'2017-10-23 19:50:33',2,1);
INSERT INTO `dataset_type` VALUES (1,'photogrammetry_image_set','2017-10-22 21:14:11',2,'2018-07-10 19:06:45',1,1),(2,'grey_card','2017-10-22 21:14:26',2,'2019-02-11 20:23:03',1,0),(3,'background_removal_image_set','2018-04-04 09:46:26',1,'2018-07-10 19:06:56',1,1),(4,'array_calibration_image_set','2018-04-04 09:46:42',1,'2018-07-10 19:07:06',1,1),(5,'grey_card_image_set','2019-02-11 15:22:51',1,'2019-02-11 20:22:51',1,1);
INSERT INTO `item_position_type` VALUES (1,'relative_to_environment','standard capture','','2017-10-22 21:35:33',2,'2018-07-10 19:07:25',1,1),(2,'relative_to_turntable','turntable capture','','2017-10-22 21:35:56',2,'2018-07-10 19:07:31',1,1);
INSERT INTO `item_type` VALUES (1,'object','2017-10-23 15:46:39',2,'2018-03-18 18:24:52',1,1),(2,'location','2017-10-23 15:46:50',2,'2017-10-23 19:46:50',2,1);
INSERT INTO `light_source_type` VALUES (1,'ambient','2017-10-22 22:26:13',2,'2017-10-23 02:26:23',2,1),(2,'strobe_standard','2017-10-22 22:26:30',2,'2018-07-10 19:07:44',1,1),(3,'strobe_cross','2017-10-22 22:26:38',2,'2018-07-10 19:07:47',1,1),(4,'patterned/structured','2017-10-22 22:26:47',2,'2017-10-23 02:26:47',2,1);
INSERT INTO `permission` VALUES (1,'user_edit','Allows user to view edit page, and save changes to users.','user','user_edit','2018-09-01 14:32:55',7,'2018-12-26 18:47:18',0,1),(3,'users_list','View list of users','user',NULL,'2018-09-02 08:00:28',7,'2018-09-02 16:07:07',7,1),(4,'view_project_details','View project details including subjects, items, etc.','content',NULL,'2018-09-02 08:00:28',7,'2018-09-02 16:06:55',7,1),(5,'create_project_details','Create subjects, items, etc. for a project.','content',NULL,'2018-09-02 08:00:28',7,'2018-09-02 16:06:55',7,1),(6,'edit_project_details','Edit subjects, items, etc. for a project','content',NULL,'2018-09-02 08:00:28',7,'2018-09-02 16:06:55',7,1),(7,'delete_project_details','Delete subjects, items, etc. for a project.','content',NULL,'2018-09-02 08:00:28',7,'2018-09-02 16:06:55',7,1),(8,'create_projects','Create projects','admin',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(9,'edit_projects','Edit projects','admin',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(10,'delete_projects','Delete projects','admin',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(11,'create_stakeholders','Create stakeholders','admin',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(12,'edit_stakeholders','Edit stakeholders','admin',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(13,'delete_stakeholders','Delete stakeholders','admin',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(14,'edit_tours','Edit tour content','content',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(15,'upload_assets','Upload assets','assets',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(16,'edit_assets','Edit/update assets','assets',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(17,'delete_assets','Delete assets','assets',NULL,'2018-09-02 08:03:55',7,'2018-09-02 16:03:55',7,1),(18,'download_processing_assets','Download processing assets','processing',NULL,'2018-09-02 08:04:32',7,'2018-09-02 16:04:32',7,1),(19,'process_models','Process models','processing',NULL,'2018-09-02 08:04:32',7,'2018-09-02 16:04:32',7,1),(20,'view_projects','View all projects','admin',NULL,'2018-09-02 08:22:18',7,'2018-09-02 16:22:18',7,1),(21,'create_edit_lookups','Create and edit lookup values','general',NULL,'2018-12-05 00:00:00',7,'2018-12-26 18:47:59',7,1);
INSERT INTO `scale_bar_barcode_type` VALUES (1,'none','2017-10-23 15:52:16',2,'2017-10-23 19:52:16',2,1),(2,'datamatrix','2017-10-23 15:52:20',2,'2017-10-23 19:52:20',2,1),(3,'qr','2017-10-23 15:52:26',2,'2018-02-10 20:16:01',1,1);
INSERT INTO `target_type` VALUES (1,'dot','2017-10-23 15:53:51',2,'2018-02-10 20:42:59',1,1),(2,'cross','2017-10-23 15:54:01',2,'2017-10-23 19:54:01',2,1),(3,'curricular_12_bit','2017-10-23 15:54:09',2,'2018-07-10 19:08:09',5,1),(4,'RAD','2017-10-23 15:54:15',2,'2018-04-04 14:03:50',1,1);
INSERT INTO `uv_map_size` VALUES (1,'1024x1024','2018-11-12 08:46:21',7,'2018-11-12 18:46:21',7,1),(2,'2048x2048','2018-11-12 08:46:21',7,'2018-11-12 18:46:21',7,1),(3,'4096x4096','2018-11-12 08:46:21',7,'2018-11-12 18:46:21',7,1),(4,'8192x8192','2018-11-12 08:46:21',7,'2018-11-12 18:46:21',7,1),(5,'No limit','2018-11-12 08:46:29',7,'2018-11-12 18:46:29',7,1);

INSERT INTO `role` (`role_id`, `rolename_canonical`, `rolename`, `role_description`, `date_created`, `created_by_user_account_id`, `last_modified`, `last_modified_user_account_id`, `active`) VALUES ('1', 'admin', 'admin', NULL, CURRENT_TIMESTAMP, '1', CURRENT_TIMESTAMP, '1', '1');
INSERT INTO `role_permission` (`role_id`, `permission_id`, `date_created`, `created_by_user_account_id`, `last_modified`, `last_modified_user_account_id`, `active`) VALUES(1, 1,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 3,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 4,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 5,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 6,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 7,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 8,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 9,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 10,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 11,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 12,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 13,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 14,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 15,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 16,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 17,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 18,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 19,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 20,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1), (1, 21,CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP,1,1);
INSERT INTO `user_role` (`user_role_id`, `username_canonical`, `stakeholder_id`, `project_id`, `role_id`, `date_created`, `created_by_user_account_id`, `last_modified`, `last_modified_user_account_id`, `active`) VALUES ('1', 'admin', NULL, NULL, '1', CURRENT_TIMESTAMP, '1', CURRENT_TIMESTAMP, '1', '1');
INSERT INTO `user_detail` (`user_detail_id`, `username_canonical`, `unit_id`, `user_type`, `date_created`, `created_by_user_account_id`, `last_modified`, `last_modified_user_account_id`, `active`) VALUES (NULL, 'admin', NULL, 'admin', CURRENT_TIMESTAMP, '1', CURRENT_TIMESTAMP, '1', '1');