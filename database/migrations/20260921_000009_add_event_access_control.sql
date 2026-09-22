-- AEFS v2
-- Eventgebonden beheerders en zichtbaarheid voor meerdere ledengroepen.
-- Deze migratie is additief en wijzigt geen bestaande leden, gebruikers,
-- evenementen of inschrijvingen.

CREATE TABLE IF NOT EXISTS `event_beheerders` (
    `event_id` INT NOT NULL,
    `lid_id` INT NOT NULL,
    `aangemaakt_door` INT NULL,
    `aangemaakt_op` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`event_id`, `lid_id`),
    KEY `idx_event_beheerders_lid` (`lid_id`, `event_id`),
    KEY `idx_event_beheerders_maker` (`aangemaakt_door`),
    CONSTRAINT `fk_event_beheerders_event`
        FOREIGN KEY (`event_id`) REFERENCES `evenementen` (`event_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_event_beheerders_lid`
        FOREIGN KEY (`lid_id`) REFERENCES `leden` (`lid_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_event_beheerders_maker`
        FOREIGN KEY (`aangemaakt_door`) REFERENCES `gebruikers` (`gebruiker_id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `event_groepen` (
    `event_id` INT NOT NULL,
    `groep_id` INT NOT NULL,
    `aangemaakt_op` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`event_id`, `groep_id`),
    KEY `idx_event_groepen_groep` (`groep_id`, `event_id`),
    CONSTRAINT `fk_event_groepen_event`
        FOREIGN KEY (`event_id`) REFERENCES `evenementen` (`event_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_event_groepen_groep`
        FOREIGN KEY (`groep_id`) REFERENCES `groepen` (`groep_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
