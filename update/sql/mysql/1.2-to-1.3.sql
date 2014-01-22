-- Add a column for log
ALTER TABLE `sqliimport_item` ADD `running_log` longtext NULL DEFAULT NULL  AFTER `progression_notes`;

