CREATE TABLE `sqliimport_scheduled` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `handler` varchar(50) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `options_serialized` longtext,
  `frequency` varchar(30) NOT NULL,
  `next` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `requested_time` int(11) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT '0',
  `manual_frequency` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `next_import` (`next`),
  KEY `import_handler` (`handler`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sqliimport_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `handler` varchar(50) DEFAULT NULL,
  `options_serialized` longtext,
  `user_id` int(11) DEFAULT NULL,
  `requested_time` int(11) DEFAULT '0',
  `status` tinyint(4) DEFAULT '0',
  `percentage_int` smallint(6) DEFAULT '0',
  `type` tinyint(4) DEFAULT '1',
  `progression_notes` longtext,
  `process_time` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `import_item_handler` (`handler`),
  KEY `import_item_user` (`user_id`),
  KEY `import_item_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;