CREATE TABLE IF NOT EXISTS `background_removal_method` (
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='This table stores background_removal_methods metadata';
CREATE TABLE IF NOT EXISTS `calibration_object_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='This table stores calibration_object_types metadata';
CREATE TABLE IF NOT EXISTS `camera_cluster_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='This table stores camera_cluster_types metadata';
CREATE TABLE IF NOT EXISTS `capture_data_element` (
  `capture_data_element_id` int(11) NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='This table stores dataset metadata';
CREATE TABLE IF NOT EXISTS `capture_data_file` (
  `capture_data_file_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_data_element_id` int(11) DEFAULT NULL,
  `capture_data_file_name` varchar(255) DEFAULT NULL,
  `capture_data_file_type` varchar(255) DEFAULT NULL,
  `is_compressed_multiple_files` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`capture_data_file_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_data_file metadata';
CREATE TABLE IF NOT EXISTS `capture_dataset` (
  `capture_dataset_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `capture_dataset_guid` varchar(255) NOT NULL DEFAULT '',
  `project_id` int(255) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='This table stores dataset metadata';
CREATE TABLE IF NOT EXISTS `capture_dataset_rights` (
  `capture_dataset_rights_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_dataset_id` int(11) DEFAULT NULL,
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
CREATE TABLE IF NOT EXISTS `capture_device` (
  `capture_device_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_data_element_id` int(11) DEFAULT NULL,
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
CREATE TABLE IF NOT EXISTS `capture_device_component` (
  `capture_device_component_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_device_id` int(11) DEFAULT NULL,
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
CREATE TABLE IF NOT EXISTS `capture_method` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='This table stores capture_methods metadata';
CREATE TABLE IF NOT EXISTS `data_rights_restriction_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='This table stores data_rights_restriction_types metadata';
CREATE TABLE IF NOT EXISTS `dataset_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='This table stores dataset_type metadata';
CREATE TABLE IF NOT EXISTS `favorite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fos_user_id` int(11) NOT NULL,
  `path` text NOT NULL,
  `page_title` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `file_package` (
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
CREATE TABLE IF NOT EXISTS `file_upload` (
  `file_upload_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `record_id` int(11) NOT NULL,
  `record_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `file_size` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_hash` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `metadata` varchar(8000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`file_upload_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2521 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `focus_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='This table stores focus_types metadata';
CREATE TABLE IF NOT EXISTS `fos_user` (
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `isni_data` (
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
CREATE TABLE IF NOT EXISTS `item` (
  `item_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `local_item_id` varchar(255) DEFAULT '',
  `item_guid` varchar(255) DEFAULT '',
  `item_description` mediumtext,
  `item_type` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`item_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2578 DEFAULT CHARSET=utf8 COMMENT='This table stores item metadata';
CREATE TABLE IF NOT EXISTS `item_position_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='This table stores item_position_types metadata';
CREATE TABLE IF NOT EXISTS `item_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='This table stores subject_types metadata';
CREATE TABLE IF NOT EXISTS `job` (
  `job_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid_` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `uuid` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `job_import_record` (
  `job_import_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `job_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `record_table` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(800) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  PRIMARY KEY (`job_import_record_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3397 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `job_log` (
  `job_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `job_log_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `job_log_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `job_log_description` varchar(800) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`job_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=326 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `light_source_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='This table stores light_source_types metadata';
CREATE TABLE IF NOT EXISTS `model` (
  `model_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `capture_dataset_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `parent_model_id` int(11) DEFAULT NULL,
  `model_guid` varchar(255) DEFAULT NULL,
  `date_of_creation` datetime DEFAULT NULL,
  `model_file_type` varchar(255) DEFAULT NULL,
  `derived_from` varchar(255) DEFAULT NULL,
  `creation_method` varchar(255) DEFAULT NULL,
  `model_modality` varchar(255) DEFAULT NULL,
  `units` varchar(255) DEFAULT NULL,
  `is_watertight` tinyint(1) DEFAULT NULL,
  `model_purpose` varchar(255) DEFAULT NULL,
  `point_count` varchar(255) DEFAULT NULL,
  `has_normals` tinyint(1) NOT NULL DEFAULT '0',
  `face_count` varchar(255) DEFAULT NULL,
  `vertices_count` varchar(255) DEFAULT NULL,
  `has_vertex_color` tinyint(1) DEFAULT NULL,
  `has_uv_space` tinyint(1) DEFAULT NULL,
  `model_maps` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`model_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='This table stores model metadata';
CREATE TABLE IF NOT EXISTS `permission` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permission_detail` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permission_group` varchar(80) COLLATE utf8_unicode_ci DEFAULT 'general',
  `route_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `photogrammetry_scale_bar` (
  `photogrammetry_scale_bar_id` int(11) NOT NULL AUTO_INCREMENT,
  `capture_dataset_id` int(11) DEFAULT NULL,
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
CREATE TABLE IF NOT EXISTS `photogrammetry_scale_bar_target_pair` (
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
CREATE TABLE IF NOT EXISTS `processing_action` (
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
CREATE TABLE IF NOT EXISTS `processing_job` (
  `processing_job_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_id` int(11) DEFAULT NULL,
  `record_type` varchar(30) DEFAULT '',
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
CREATE TABLE IF NOT EXISTS `processing_job_file` (
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
CREATE TABLE IF NOT EXISTS `project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) DEFAULT '',
  `stakeholder_guid` varchar(255) DEFAULT '',
  `project_description` text,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`project_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`),
  KEY `projects_label` (`project_name`,`stakeholder_guid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='This table stores project metadata';
CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int(11) NOT NULL,
  `rolename_canonical` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `rolename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role_description` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `role_permission` (
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
CREATE TABLE IF NOT EXISTS `scale_bar_barcode_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='This table stores scale_bar_barcode_types metadata';
CREATE TABLE IF NOT EXISTS `status_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='This table stores status_type metadata';
CREATE TABLE IF NOT EXISTS `subject` (
  `subject_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `local_subject_id` varchar(255) DEFAULT '',
  `subject_guid` varchar(255) DEFAULT '',
  `subject_name` varchar(255) DEFAULT '',
  `subject_display_name` varchar(255) DEFAULT NULL,
  `holding_entity_name` varchar(255) DEFAULT '',
  `holding_entity_guid` varchar(255) DEFAULT '',
  `date_created` varchar(255) NOT NULL DEFAULT '',
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`subject_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`),
  KEY `projects_id` (`project_id`,`subject_name`)
) ENGINE=InnoDB AUTO_INCREMENT=795 DEFAULT CHARSET=utf8 COMMENT='This table stores subject metadata';
CREATE TABLE IF NOT EXISTS `subject_type` (
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
CREATE TABLE IF NOT EXISTS `target_type` (
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='This table stores target_types metadata';
CREATE TABLE IF NOT EXISTS `unit_stakeholder` (
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
) ENGINE=InnoDB AUTO_INCREMENT=849 DEFAULT CHARSET=utf8 COMMENT='This table stores unit_stakeholder metadata';
CREATE TABLE IF NOT EXISTS `unit` (
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='This table stores unit metadata';
CREATE TABLE IF NOT EXISTS `user_detail` (
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
CREATE TABLE IF NOT EXISTS `user_role` (
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
CREATE TABLE IF NOT EXISTS `uv_map` (
  `uv_map_id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) DEFAULT NULL,
  `capture_dataset_id` int(11) DEFAULT NULL,
  `map_type` varchar(255) DEFAULT NULL,
  `map_file_type` varchar(255) DEFAULT NULL,
  `map_size` varchar(255) DEFAULT NULL,
  `file_path` varchar(8000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_checksum` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `file_package_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`uv_map_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`),
  KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores uv_map metadata';
CREATE TABLE IF NOT EXISTS `vz_export` (
  `export_id` int(11) NOT NULL AUTO_INCREMENT,
  `specimen_type` varchar(1000) NOT NULL,
  `scan_nid` int(11) NOT NULL,
  `scan_title` varchar(1000) NOT NULL,
  `specimen_nid` int(11) NOT NULL,
  `specimen_title` varchar(1000) NOT NULL,
  `field_flash_path_value` varchar(2000) NOT NULL,
  `body` varchar(8000) NOT NULL,
  `body2` varchar(8000) NOT NULL,
  PRIMARY KEY (`export_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2971 DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `workflow` (
  `workflow_id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`workflow_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `workflow_status_log` (
  `workflow_status_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `record_table` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `processing_step` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status_detail` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `created_by_user_account_id` int(11) NOT NULL,
  PRIMARY KEY (`workflow_status_log_id`),
  KEY `created_by_user_account_id` (`created_by_user_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE model add column model_id int(11) DEFAULT NULL;

ALTER TABLE uv_map add column model_file_id int(11) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `model_file` (
  `model_file_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `file_upload_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_account_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified_user_account_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) COMMENT='This table stores model_file metadata';

ALTER TABLE `model_file`
  ADD PRIMARY KEY (`model_file_id`),
  ADD KEY `created_by_user_account_id` (`created_by_user_account_id`),
  ADD KEY `last_modified_user_account_id` (`last_modified_user_account_id`);

ALTER TABLE `model_file`
  MODIFY `model_file_id` int(11) NOT NULL AUTO_INCREMENT;

INSERT INTO `background_removal_method` VALUES (1,'none','2017-10-23 02:00:55',2,'2018-02-10 18:27:53',1,1),(2,'clip_white','2017-10-23 02:01:18',2,'2018-07-10 19:05:10',5,1),(3,'clip_black','2017-10-23 02:01:24',2,'2018-07-10 19:05:14',2,1),(4,'background_subtraction_by_image_set','2017-10-23 02:01:35',2,'2018-07-10 19:05:36',2,1);
INSERT INTO `calibration_object_type` VALUES (1,'scale_bar','2017-10-23 16:01:47',2,'2018-07-10 19:05:42',2,1),(2,'gray_card','2017-10-23 16:01:54',2,'2018-07-10 19:05:44',2,1),(3,'unknown','2017-10-23 16:02:01',2,'2018-04-04 14:04:12',1,1);
INSERT INTO `camera_cluster_type` VALUES (1,'none','2017-10-23 02:04:06',2,'2018-04-04 13:59:11',5,1),(2,'array','2017-10-23 02:04:12',2,'2018-04-04 13:59:11',5,1),(3,'spherical_image_station','2017-10-23 02:04:20',2,'2018-07-10 19:05:53',1,1),(4,'focal_stack_position_based','2017-10-23 02:04:28',2,'2018-07-10 19:06:02',5,1),(13,'focal_stack_focus_based','2018-04-02 10:18:54',1,'2018-07-10 19:06:11',1,1);
INSERT INTO `data_rights_restriction_type` VALUES (1,'none','2017-10-23 15:49:48',2,'2018-02-10 18:44:00',1,1),(2,'copyrighted','2017-10-23 15:50:07',2,'2017-10-23 19:50:07',2,1),(3,'culturally_sensitive','2017-10-23 15:50:15',2,'2018-07-10 19:06:25',2,1),(4,'si_terms_of_use','2017-10-23 15:50:26',2,'2018-07-10 19:06:34',1,1),(5,'embargo','2017-10-23 15:50:33',2,'2017-10-23 19:50:33',2,1);
INSERT INTO `dataset_type` VALUES (1,'photogrammetry_image_set','2017-10-22 21:14:11',2,'2018-07-10 19:06:45',1,1),(2,'grey_card','2017-10-22 21:14:26',2,'2018-07-10 19:06:48',2,1),(3,'background_removal_image_set','2018-04-04 09:46:26',1,'2018-07-10 19:06:56',1,1),(4,'array_calibration_image_set','2018-04-04 09:46:42',1,'2018-07-10 19:07:06',1,1);
INSERT INTO `item_position_type` VALUES (1,'relative_to_environment','standard capture','','2017-10-22 21:35:33',2,'2018-07-10 19:07:25',1,1),(2,'relative_to_turntable','turntable capture','','2017-10-22 21:35:56',2,'2018-07-10 19:07:31',1,1);
INSERT INTO `item_type` (`item_type_id`, `label`, `date_created`, `created_by_user_account_id`, `last_modified`, `last_modified_user_account_id`, `active`)VALUES (1,'object','2017-10-23 15:46:39',2,'2018-03-18 14:24:52',1,1), (2,'location','2017-10-23 15:46:50',2,'2017-10-23 15:46:50',2,1);
INSERT INTO `light_source_type` VALUES (1,'ambient','2017-10-22 22:26:13',2,'2017-10-23 02:26:23',2,1),(2,'strobe_standard','2017-10-22 22:26:30',2,'2018-07-10 19:07:44',1,1),(3,'strobe_cross','2017-10-22 22:26:38',2,'2018-07-10 19:07:47',1,1),(4,'patterned/structured','2017-10-22 22:26:47',2,'2017-10-23 02:26:47',2,1);
INSERT INTO `target_type` VALUES (1,'dot','2017-10-23 15:53:51',2,'2018-02-10 20:42:59',1,1),(2,'cross','2017-10-23 15:54:01',2,'2017-10-23 19:54:01',2,1),(3,'curricular_12_bit','2017-10-23 15:54:09',2,'2018-07-10 19:08:09',5,1),(4,'RAD','2017-10-23 15:54:15',2,'2018-04-04 14:03:50',1,1);
