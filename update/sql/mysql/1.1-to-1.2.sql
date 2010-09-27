-- Add a relation to sqliimport_scheduled when necessary
ALTER TABLE `sqliimport_item` ADD `scheduled_id` int NULL DEFAULT NULL  AFTER `process_time`;
ALTER TABLE `sqliimport_item` ADD INDEX `import_scheduled_id` (`scheduled_id`);

