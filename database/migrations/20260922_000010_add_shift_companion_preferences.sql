-- Bevestigingsmail wordt per inschrijving vastgelegd; de keuzeperiode start
-- pas wanneer de ontvanger van die specifieke mailing werkelijk is bereikt.
ALTER TABLE `event_inschrijvingen`
    ADD COLUMN `voorkeur_mailing_id` BIGINT UNSIGNED NULL AFTER `status`,
    ADD KEY `idx_event_inschrijvingen_voorkeur_mailing` (`voorkeur_mailing_id`),
    ADD CONSTRAINT `fk_event_inschrijvingen_voorkeur_mailing`
        FOREIGN KEY (`voorkeur_mailing_id`) REFERENCES `mailings` (`mailing_id`)
        ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `event_shift_voorkeuren` (
    `event_id` INT NOT NULL,
    `lid_id` INT NOT NULL,
    `voorkeur_mailing_id` BIGINT UNSIGNED NOT NULL,
    `gewenst_lid_id` INT NOT NULL,
    `aangemaakt_op` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`event_id`, `lid_id`, `voorkeur_mailing_id`, `gewenst_lid_id`),
    KEY `idx_event_shift_voorkeuren_gewenst` (`event_id`, `gewenst_lid_id`),
    CONSTRAINT `fk_event_shift_voorkeuren_event`
        FOREIGN KEY (`event_id`) REFERENCES `evenementen` (`event_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_event_shift_voorkeuren_lid`
        FOREIGN KEY (`lid_id`) REFERENCES `leden` (`lid_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_event_shift_voorkeuren_mailing`
        FOREIGN KEY (`voorkeur_mailing_id`) REFERENCES `mailings` (`mailing_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_event_shift_voorkeuren_gewenst_lid`
        FOREIGN KEY (`gewenst_lid_id`) REFERENCES `leden` (`lid_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
