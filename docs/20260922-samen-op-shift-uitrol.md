# AEFS v2: uitrol van bevestigingsmails en samen-op-shiftvoorkeuren

Dit is een upgrade van de bestaande one.com-installatie. De migratie is
additief: ze verwijdert of overschrijft geen leden, gebruikers, bestaande
evenementinschrijvingen of mailhistoriek. Een SFTP-upload voert geen SQL uit.

## Bestanden

- FileZilla-webroot: `build/aefs-v2-one-com-upload-20260922-samen-op-shift/`
- Controleerbaar archief met dezelfde inhoud:
  `build/aefs-v2-one-com-20260922-samen-op-shift.zip`
- SQL voor phpMyAdmin, **niet** in de webroot uploaden:
  `database/migrations/20260922_000010_add_shift_companion_preferences.sql`

Upload de **inhoud** van de uitgepakte webrootmap naar de bestaande one.com
`httpd.www`-map, niet de bovenliggende map zelf. Gebruik in FileZilla
*Overschrijven*, geen synchronisatie met verwijderen. Laat alle bestaande
bestanden in `config/local/` en `storage/` op de server staan. De uploadmap
bevat daar geen productiegeheimen of operationele data. Controleer dat de
verborgen `.htaccess` in de webroot aanwezig blijft.

## Volgorde en veiligheidscontroles

1. Leg een onderhoudsvenster vast en maak een verse back-up van de **huidige
   productie**database en webroot. Verifieer dat de back-up terug te zetten is.
   Bewaar ook de stabiele productie-`app_key`; genereer geen nieuwe sleutel.
2. Noteer vóór de migratie de resultaten van deze alleen-lezen controles in
   phpMyAdmin:

   ```sql
   SELECT COUNT(*) AS leden FROM leden;
   SELECT COUNT(*) AS gebruikers FROM gebruikers;
   SELECT COUNT(*) AS inschrijvingen FROM event_inschrijvingen;
   SHOW TABLES LIKE 'event_beheerders';
   SHOW TABLES LIKE 'event_groepen';
   SHOW COLUMNS FROM event_inschrijvingen LIKE 'voorkeur_mailing_id';
   SHOW TABLES LIKE 'event_shift_voorkeuren';
   ```

3. Als `event_beheerders` of `event_groepen` nog ontbreekt, voer **eerst** de
   bestaande additieve migratie
   `database/migrations/20260921_000009_add_event_access_control.sql` uit.
   Zijn de twee nieuwe voorkeurstructuren al beide aanwezig, voer migratie
   `000010` niet opnieuw uit. Is slechts één van de twee aanwezig, stop dan:
   dat is een gedeeltelijke migratie die gericht moet worden gecontroleerd.
4. Importeer eenmaal
   `database/migrations/20260922_000010_add_shift_companion_preferences.sql`
   in de bestaande productiedatabase via phpMyAdmin. Dit voegt
   `event_inschrijvingen.voorkeur_mailing_id` en `event_shift_voorkeuren` toe.
   Importeer **niet** `database/database.sql` en herstel geen oude cutoverdump
   over recente productiegegevens.
5. Controleer opnieuw de drie aantallen uit stap 2; ze moeten identiek zijn.
   Controleer de nieuwe schemaonderdelen met:

   ```sql
   SHOW COLUMNS FROM event_inschrijvingen LIKE 'voorkeur_mailing_id';
   SHOW CREATE TABLE event_shift_voorkeuren;
   SELECT COUNT(*) AS voorkeuren FROM event_shift_voorkeuren;
   SELECT COUNT(*) AS ongekoppelde_mails
   FROM event_inschrijvingen ei
   LEFT JOIN mailings m ON m.mailing_id = ei.voorkeur_mailing_id
   WHERE ei.voorkeur_mailing_id IS NOT NULL AND m.mailing_id IS NULL;
   ```

   Bij een verse migratie zijn `voorkeuren` en `ongekoppelde_mails` beide 0.
6. Upload pas daarna de uitgepakte webroot via FileZilla. Overschrijf
   productieconfiguratie, `storage/` en gebruikersuploads niet. Wis ook geen
   bestaande bestanden die niet in het pakket zitten.
7. Controleer login, profiel, evenement, shiftplanning en het Excelrapport.
   Controleer vervolgens of de bestaande externe mailworker actief blijft.
   Maak voor een echte acceptatiemail uitsluitend een testevenement dat via
   `event_groepen` beperkt is tot een aparte groep met testleden; publiceren
   zonder groep mailt potentieel **alle** actieve leden.

## Functionele acceptatie

Bevestig twee of meer testleden en gebruik daarna één keer de knop
*Bevestigingsmails versturen*. Het mailoverzicht moet één mailing met de juiste
ontvangers tonen. Vóór werkelijke aflevering is er op hun profielen nog geen
keuzevenster. Na aflevering verschijnt het venster met andere bevestigde
deelnemers; de deadline is per ontvanger exact zeven dagen na `verzonden_op`.
Een beheerder kan de leden afzonderlijk via de popup aan een shift toevoegen;
een volgende popup toont de voorkeuren van de zojuist toegevoegde deelnemer.
Controleer ook dat volle shifts en onbeschikbare eventdagen niet kunnen worden
omzeild. Download ten slotte het Excelbestand voor het evenement en voor één
shift; beide mogen alleen namen en voorkeuren bevatten.

## Terugval

Bewaar het vorige webpakket. Bij een fout kan de vorige code teruggezet worden
zonder de nieuwe, additieve tabellen of kolom te verwijderen. Zet de site zo
nodig tijdelijk buiten gebruik en onderzoek de fout vóór de mailworker nieuwe
bulkmail verwerkt. Herstel de databaseback-up alleen na een expliciete
analyse van eventuele nieuwere productiegegevens; verwijder de nieuwe
voorkeurentabel of mailhistoriek niet blindelings.
