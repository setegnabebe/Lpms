ALTER TABLE `requests` ADD `GM` VARCHAR(100) NULL AFTER `manager`;
ALTER TABLE `requests` ADD `phase_one` INT NOT NULL DEFAULT '1' AFTER `directors`, ADD `phase_two` INT NULL DEFAULT NULL AFTER `phase_one`, ADD `phase_three` INT NULL DEFAULT NULL AFTER `phase_two`, ADD `phase_four` INT NULL DEFAULT NULL AFTER `phase_three`;
ALTER TABLE `requests` CHANGE `phase_two` `phase_two` INT NOT NULL DEFAULT '0', CHANGE `phase_three` `phase_three` INT NOT NULL DEFAULT '0', CHANGE `phase_four` `phase_four` INT NOT NULL DEFAULT '0';
ALTER TABLE `report` ADD `GM_approval_date` TIMESTAMP NULL AFTER `manager_approval_date`;
-- Fleet
ALTER TABLE `comp` ADD `With GM` VARCHAR(500) NULL AFTER `hr_main`;

-- NEW
ALTER TABLE `catagory` ADD `description` VARCHAR(300) NULL AFTER `display_name`;