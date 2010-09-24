-- Add a process_length column
-- Seconds elapsed for import item will be stored here
ALTER TABLE `sqliimport_item` ADD `process_time` int UNSIGNED NULL DEFAULT '0'  AFTER `progression_notes`;

-- Manual frequency
ALTER TABLE `sqliimport_scheduled` ADD `manual_frequency` int NULL DEFAULT '0'  AFTER `is_active`;
