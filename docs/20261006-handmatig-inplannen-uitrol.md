# AEFS v2: uitrol handmatige event- en shifttoewijzing

Dit pakket is een **code-update** van de bestaande one.com-installatie. Voor
deze uitbreiding is geen nieuwe SQL-migratie nodig. Importeer daarom geen
`database/database.sql` en vervang de productiedatabase niet.

## Bestanden

- Uitgepakte FileZilla-webroot:
  `build/aefs-v2-one-com-upload-20261006-handmatig-inplannen/`
- Archief met dezelfde inhoud:
  `build/aefs-v2-one-com-20261006-handmatig-inplannen.zip`

Upload de **inhoud** van de uitgepakte webrootmap naar de bestaande one.com-map
`httpd.www`, niet de bovenliggende map zelf. Kies in FileZilla *Overschrijven*
en gebruik geen synchronisatie of optie die bestanden op de server verwijdert.
De map `config/local/` bevat in het pakket alleen voorbeeldbestanden; bewaar
alle bestaande productieconfiguratie, de stabiele `app_key`, `storage/` en
gebruikersuploads op de server. Controleer dat `.htaccess` in de webroot
aanwezig blijft.

## Vóór de upload

1. Maak een verse back-up van de huidige productiedatabase en webroot en
   controleer of die terug te zetten is.
2. Controleer dat de vorige uitrol van de voorkeuren voor samen op een shift
   al volledig is toegepast. Dit pakket vervangt die migratie niet.
3. Plan een kort onderhoudsvenster. Maak geen onbeperkt zichtbaar testevent:
   publicatie kan automatisch een mailing aan alle in aanmerking komende
   leden klaarzetten.

## Na de upload

1. Controleer login, dashboard, een bestaand evenement en de shiftplanning.
2. Controleer op de eventpagina dat een admin en de toegewezen eventbeheerder
   een bestaand, geschikt lid kunnen toevoegen. Een eventbeheerder mag geen
   ander evenement of vertrouwelijke lidvelden te zien krijgen.
3. Gebruik voor een echte functionele test uitsluitend een afgeschermd
   testevenement met een beperkte ledengroep en een testlid met geldig
   e-mailadres. Voeg het lid toe aan het evenement en daarna aan een shift.
   Controleer dat de eventinschrijving bevestigd is, de shiftbezetting klopt
   en de mail met datum en uren in de wachtrij verschijnt en wordt afgeleverd.
   Een reserve-toewijzing mag nog geen mail suggereren dat het lid definitief
   ingepland is.
4. Controleer de bestaande externe mailworker en de mailinghistoriek. Een
   geslaagde webpagina of wachtrijstatus alleen bewijst nog geen aflevering.

Voer de muterende acceptatietest pas uit na afstemming over het testevenement,
testlid en het werkelijke ontvangeradres. Zonder die gegevens beperken we de
livecontrole tot alleen-lezen controles.

## Terugval

Bewaar de vorige webbestanden. Bij een regressie kan de vorige code worden
teruggezet zonder een databasemigratie terug te draaien. Verwijder geen nieuw
ontstane event-, shift- of mailhistoriek. Analyseer eerst de toestand van de
mailwachtrij voordat de worker eventuele testmails opnieuw verwerkt.
