<?php

declare(strict_types=1);

use AEFS\Core\Auth;
use AEFS\Core\Config;
use AEFS\Core\Container;
use AEFS\Core\Database;
use AEFS\Core\Http\Request;
use AEFS\Core\Session;
use AEFS\Core\View\Helper\CsrfHelper;
use AEFS\Core\View\ViewFactory;
use App\Controllers\EventController;
use App\Controllers\ProfileController;
use App\Controllers\ReportController;
use App\Controllers\ShiftController;
use App\Mail\RecipientPolicy;
use App\Repositories\EventRepository;
use App\Repositories\MailingRepository;
use App\Services\EventAccessService;
use App\Services\EventCompanionService;
use App\Services\EventRegistrationProfileService;
use App\Services\EventService;
use App\Services\MemberService;
use App\Services\ReportExcelExportService;
use App\Services\ReportService;
use App\Services\SettingsService;
use App\Services\ShiftService;

if (PHP_SAPI !== 'cli') {
    throw new RuntimeException('Deze test mag uitsluitend lokaal via de CLI lopen.');
}

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
/** @var Container $container */
$container = $app->container();
/** @var Config $config */
$config = $container->get(Config::class);
if ((string) $config->get('app.environment', '') === 'production') {
    throw new RuntimeException('Deze transactietest mag niet op productie lopen.');
}
$connectionName = (string) $config->get('database.default', 'mysql');
$databaseHost = (string) $config->get('database.connections.' . $connectionName . '.host', '');
if (!in_array($databaseHost, ['localhost', '127.0.0.1', '::1'], true)) {
    throw new RuntimeException('Deze transactietest vereist een lokale databasehost.');
}

/** @var Database $database */
$database = $container->get(Database::class);
$pdo = $database->pdo();
$policy = $container->get(RecipientPolicy::class);
$mailRepository = $container->get(MailingRepository::class);
$companions = $container->get(EventCompanionService::class);
$eventService = $container->get(EventService::class);
$shiftService = $container->get(ShiftService::class);
$reports = $container->get(ReportService::class);
$exporter = $container->get(ReportExcelExportService::class);
$views = $container->get(ViewFactory::class);
$csrf = $container->get(CsrfHelper::class);

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};
$validateExcel = static function (string $xlsx) use ($assert): void {
    $assert(str_starts_with($xlsx, 'PK'), 'De Excel-export is geen ZIP/XLSX.');
    $path = tempnam(sys_get_temp_dir(), 'aefs-flow-xlsx-');
    $assert($path !== false, 'Er kon geen tijdelijk Excelbestand worden gemaakt.');
    try {
        file_put_contents($path, $xlsx);
        $archive = new ZipArchive();
        $assert($archive->open($path) === true, 'Het Excelarchief kan niet worden geopend.');
        try {
            for ($i = 0; $i < $archive->numFiles; $i++) {
                $name = $archive->getNameIndex($i);
                if (!str_ends_with($name, '.xml') && !str_ends_with($name, '.rels')) {
                    continue;
                }
                $xml = new DOMDocument();
                $assert($xml->loadXML((string) $archive->getFromIndex($i)), 'Ongeldige Excel-XML: ' . $name);
            }
            $sheet = (string) $archive->getFromName('xl/worksheets/sheet1.xml');
            $assert(str_contains($sheet, 'Wil samen met'), 'De voorkeurkolom ontbreekt.');
            $assert(!str_contains($sheet, 'rekeningnummer') && !str_contains($sheet, 'rijksregisternummer'), 'Het Excelblad bevat vertrouwelijke velden.');
        } finally {
            $archive->close();
        }
    } finally {
        unlink($path);
    }
};
$counts = static fn(): array => $database->query(<<<'SQL'
    SELECT (SELECT COUNT(*) FROM leden) AS leden,
           (SELECT COUNT(*) FROM gebruikers) AS gebruikers,
           (SELECT COUNT(*) FROM evenementen) AS evenementen,
           (SELECT COUNT(*) FROM event_inschrijvingen) AS event_inschrijvingen,
           (SELECT COUNT(*) FROM shifts) AS shifts,
           (SELECT COUNT(*) FROM shift_inschrijvingen) AS shift_inschrijvingen,
           (SELECT COUNT(*) FROM mailings) AS mailings,
           (SELECT COUNT(*) FROM event_shift_voorkeuren) AS voorkeuren
    SQL)->fetch();
$before = $counts();

$members = [];
$addresses = [];
foreach ($database->query(<<<'SQL'
    SELECT l.lid_id FROM leden l WHERE l.actief = 1
    ORDER BY EXISTS (
        SELECT 1 FROM gebruikers u
        WHERE u.lid_id = l.lid_id AND u.actief = 1
          AND u.rol = 'lid' AND u.goedkeuringsstatus = 'goedgekeurd'
    ) DESC, l.lid_id
    SQL)->fetchAll() as $row) {
    $member = $mailRepository->eligibleMember((int) $row['lid_id']);
    if ($member === null || !$policy->allows((string) $member['email'])) {
        continue;
    }
    $address = strtolower((string) $member['email']);
    if (isset($addresses[$address])) {
        continue;
    }
    $members[] = $member;
    $addresses[$address] = true;
    if (count($members) === 3) {
        break;
    }
}
$assert(count($members) === 3, 'Er zijn drie verschillende toegestane lokale testadressen nodig.');
$admin = $database->query("SELECT gebruiker_id, lid_id, email FROM gebruikers WHERE rol = 'admin' AND actief = 1 LIMIT 1")->fetch();
$assert(is_array($admin), 'Er is geen lokaal adminaccount voor de sessiesimulatie.');

$asUser = static function (int $id, ?int $memberId, string $role, string $email): void {
    Session::set('auth', [
        'gebruiker_id' => $id,
        'lid_id' => $memberId,
        'email' => $email,
        'rol' => $role,
    ]);
};
$asAdmin = static function () use ($asUser, $admin): void {
    $asUser((int) $admin['gebruiker_id'], (int) $admin['lid_id'], 'admin', (string) $admin['email']);
};
$newRequest = static function (array $query = [], array $post = [], array $route = []): Request {
    $request = new Request($query, $post, [
        'REQUEST_METHOD' => $post === [] ? 'GET' : 'POST',
        'REQUEST_URI' => '/',
        'HTTP_HOST' => 'localhost',
    ]);
    $request->setRouteParameters(array_map('strval', $route));
    return $request;
};
$eventController = static fn(Request $request): EventController => new EventController(
    $views,
    $request,
    $eventService,
    $container->get(EventAccessService::class),
    $container->get(EventRegistrationProfileService::class),
    $container->get(SettingsService::class),
    $csrf
);
$profileController = static fn(Request $request): ProfileController => new ProfileController(
    $views,
    $request,
    $container->get(MemberService::class),
    $companions,
    $csrf
);
$shiftController = static fn(Request $request): ShiftController => new ShiftController(
    $views,
    $request,
    $shiftService,
    $companions,
    $container->get(EventAccessService::class),
    $container->get(SettingsService::class),
    $container->get(EventRepository::class),
    $csrf
);
$reportController = static fn(Request $request): ReportController => new ReportController(
    $views,
    $request,
    $reports,
    $container->get(EventAccessService::class),
    $exporter
);

$pdo->beginTransaction();
try {
    $date = (new DateTimeImmutable('+30 days'))->format('Y-m-d');
    $nextDate = (new DateTimeImmutable('+31 days'))->format('Y-m-d');
    $database->execute(<<<'SQL'
        INSERT INTO evenementen (titel, startdatum, einddatum, status)
        VALUES ('AEFS lokale flowtest', :startdatum, :einddatum, 'gepubliceerd')
        SQL, ['startdatum' => $date, 'einddatum' => $nextDate]);
    $eventId = (int) $pdo->lastInsertId();
    $typeId = (int) $database->query('SELECT type_id FROM shift_types WHERE actief = 1 ORDER BY type_id LIMIT 1')->fetchColumn();
    $assert($typeId > 0, 'Er is geen actief shifttype beschikbaar.');

    $registrationIds = [];
    foreach ($members as $member) {
        $database->execute(<<<'SQL'
            INSERT INTO event_inschrijvingen (event_id, lid_id, status)
            VALUES (:event_id, :lid_id, 'wachtend')
            SQL, ['event_id' => $eventId, 'lid_id' => $member['lid_id']]);
        $registrationId = (int) $pdo->lastInsertId();
        $registrationIds[] = $registrationId;
        $database->execute(<<<'SQL'
            INSERT INTO event_inschrijving_dagen (inschrijving_id, datum)
            VALUES (:id, :datum)
            SQL, ['id' => $registrationId, 'datum' => $date]);
    }

    $asAdmin();
    foreach ($registrationIds as $id) {
        $eventService->approveRegistration($id);
    }
    $queuedBefore = (int) $database->query("SELECT COUNT(*) FROM mailings WHERE event_id = $eventId AND type = 'event_bevestigd'")->fetchColumn();
    $assert($queuedBefore === 0, 'Bevestigen alleen heeft al mail ingepland.');

    $response = $eventController($newRequest([], ['_token' => $csrf->token()], ['id' => $eventId]))->sendConfirmations();
    $assert($response->status() === 302, 'De groepsknop gaf geen redirect.');
    $mailing = $database->query("SELECT mailing_id FROM mailings WHERE event_id = $eventId AND type = 'event_bevestigd'")->fetch();
    $assert(is_array($mailing), 'Er werd geen bevestigingsmailing aangemaakt.');
    $mailingId = (int) $mailing['mailing_id'];
    $recipients = $database->query("SELECT lid_id, inhoud_tekst FROM mailing_ontvangers WHERE mailing_id = $mailingId ORDER BY lid_id")->fetchAll();
    $assert(count($recipients) === 3, 'De groepsmail bevat niet alle drie bevestigde deelnemers.');
    foreach ($recipients as $recipient) {
        $assert(str_contains((string) $recipient['inhoud_tekst'], '7 dagen'), 'De mail noemt de keuzeperiode niet.');
    }
    $assert($companions->choicesForMember((int) $members[0]['lid_id']) === [], 'De keuze is open vóór aflevering.');

    $database->execute("UPDATE mailing_ontvangers SET status = 'verzonden', verzonden_op = NOW() WHERE mailing_id = :id", ['id' => $mailingId]);
    $ids = array_map(static fn(array $member): int => (int) $member['lid_id'], $members);
    foreach ($members as $member) {
        $asUser((int) $admin['gebruiker_id'], (int) $member['lid_id'], 'lid', (string) $member['email']);
        $profile = $profileController($newRequest())->show();
        $assert($profile->status() === 200 && str_contains($profile->content(), 'Samen op een shift'), 'Het profiel toont de keuze niet.');
    }
    foreach ([[0, 1], [1, 2], [2, 0]] as [$source, $target]) {
        $asUser((int) $admin['gebruiker_id'], $ids[$source], 'lid', (string) $members[$source]['email']);
        $response = $profileController($newRequest([], [
            '_token' => $csrf->token(),
            'lid_ids' => [(string) $ids[$target]],
        ], ['eventId' => $eventId]))->saveShiftCompanions();
        $assert($response->status() === 302, 'Het opslaan van een voorkeur gaf geen redirect.');
        $assert($companions->selectedIds($eventId, $ids[$source]) === [$ids[$target]], 'Een voorkeur werd niet opgeslagen.');
    }

    $database->execute(<<<'SQL'
        INSERT INTO shifts (event_id, type_id, naam, start_op, eind_op, max_personen)
        VALUES (:event_id, :type_id, 'Flowtest shift', :start_op, :eind_op, 3)
        SQL, ['event_id' => $eventId, 'type_id' => $typeId, 'start_op' => "$date 12:00:00", 'eind_op' => "$date 18:00:00"]);
    $shiftId = (int) $pdo->lastInsertId();
    $asAdmin();
    $assign = static function (int $memberId, int $sourceId, string $chain) use ($shiftController, $newRequest, $csrf, $shiftId): string {
        $post = ['_token' => $csrf->token(), 'lid_id' => (string) $memberId, 'status' => 'bevestigd'];
        if ($sourceId > 0) {
            $post['companion_from'] = (string) $sourceId;
            $post['companion_chain'] = $chain;
        }
        $response = $shiftController($newRequest([], $post, ['id' => $shiftId]))->assign();
        return (string) $response->headers()->get('Location');
    };
    $location = $assign($ids[0], 0, '');
    $assert(str_contains($location, 'companion_chain=' . $ids[0]), 'De eerste toewijzing opent geen vervolg.');
    $popup = $shiftController($newRequest(['companion_chain' => (string) $ids[0]], [], ['id' => $shiftId]))->show()->content();
    $assert(str_contains($popup, 'Samen op deze shift?') && str_contains($popup, (string) $ids[1]), 'De eerste popup toont de gekozen deelnemer niet.');
    $location = $assign($ids[1], $ids[0], (string) $ids[0]);
    $assert(str_contains($location, rawurlencode($ids[0] . ',' . $ids[1])), 'De tweede toewijzing opent geen recursief vervolg.');
    $popup = $shiftController($newRequest(['companion_chain' => $ids[0] . ',' . $ids[1]], [], ['id' => $shiftId]))->show()->content();
    $assert(str_contains($popup, 'Samen op deze shift?') && str_contains($popup, (string) $ids[2]), 'De tweede popup toont de volgende voorkeur niet.');
    $assign($ids[2], $ids[1], $ids[0] . ',' . $ids[1]);
    $closed = $shiftController($newRequest(['companion_chain' => implode(',', $ids)], [], ['id' => $shiftId]))->show()->content();
    $assert(!str_contains($closed, 'Samen op deze shift?'), 'Een cirkel in voorkeuren opent opnieuw een popup.');

    $report = $reports->shiftCompanions($eventId);
    $shiftReport = $reports->shiftCompanions($eventId, $shiftId);
    $assert($report !== null && count($report['groups']) === 1, 'Het eventrapport groepeert de cyclus niet.');
    $assert($shiftReport !== null && count($shiftReport['groups'][0]['members']) === 3, 'Het shiftrapport mist deelnemers.');
    foreach ([$report, $shiftReport] as $data) {
        $validateExcel($exporter->exportCompanions($data));
    }
    $download = $reportController($newRequest(['event_id' => $eventId, 'shift_id' => $shiftId]))->shiftCompanionsExport();
    $assert($download->status() === 200 && str_starts_with($download->content(), 'PK'), 'De downloadroute levert geen Excelbestand.');

    $asUser((int) $admin['gebruiker_id'], $ids[0], 'lid', (string) $members[0]['email']);
    $denied = $reportController($newRequest(['event_id' => $eventId]))->shiftCompanionsExport();
    $assert($denied->status() === 403, 'Een gewoon lid kan het beheerrapport downloaden.');
    $database->execute(<<<'SQL'
        INSERT INTO event_beheerders (event_id, lid_id, aangemaakt_door)
        VALUES (:event_id, :lid_id, :admin_id)
        SQL, ['event_id' => $eventId, 'lid_id' => $ids[0], 'admin_id' => $admin['gebruiker_id']]);
    $allowed = $reportController($newRequest(['event_id' => $eventId]))->shiftCompanionsExport();
    $assert($allowed->status() === 200, 'Een toegewezen eventbeheerder kan het rapport niet downloaden.');
    $asAdmin();

    $database->execute(<<<'SQL'
        INSERT INTO shifts (event_id, type_id, naam, start_op, eind_op, max_personen)
        VALUES (:event_id, :type_id, 'Volle testshift', :start_op, :eind_op, 1)
        SQL, ['event_id' => $eventId, 'type_id' => $typeId, 'start_op' => "$date 19:00:00", 'eind_op' => "$date 21:00:00"]);
    $fullShiftId = (int) $pdo->lastInsertId();
    $shiftService->assignByAdmin($fullShiftId, $ids[0], 'bevestigd');
    try {
        $shiftService->assignByAdmin($fullShiftId, $ids[1], 'bevestigd');
        throw new RuntimeException('Een volle shift liet een extra bevestigde deelnemer toe.');
    } catch (DomainException) {
    }

    $database->execute(<<<'SQL'
        INSERT INTO shifts (event_id, type_id, naam, start_op, eind_op, max_personen)
        VALUES (:event_id, :type_id, 'Andere dag', :start_op, :eind_op, 2)
        SQL, ['event_id' => $eventId, 'type_id' => $typeId, 'start_op' => "$nextDate 12:00:00", 'eind_op' => "$nextDate 18:00:00"]);
    $otherDayShiftId = (int) $pdo->lastInsertId();
    try {
        $shiftService->assignByAdmin($otherDayShiftId, $ids[0], 'bevestigd');
        throw new RuntimeException('Een niet-gekozen eventdag liet een shifttoewijzing toe.');
    } catch (DomainException) {
    }

    $database->execute('UPDATE mailing_ontvangers SET verzonden_op = DATE_SUB(NOW(), INTERVAL 8 DAY) WHERE mailing_id = :mailing_id AND lid_id = :lid_id', [
        'mailing_id' => $mailingId,
        'lid_id' => $ids[0],
    ]);
    try {
        $companions->saveChoices($eventId, $ids[0], []);
        throw new RuntimeException('Een voorkeur na de deadline werd toegestaan.');
    } catch (DomainException) {
    }

    echo "Controller- en servicestroom geslaagd: bevestiging, batchmail, aflevering, profiel, recursieve popup, toegangsrechten, capaciteit, eventdagen, rapport, Excel en deadline.\n";
} finally {
    $pdo->rollBack();
    Session::remove('auth');
}

$assert($before === $counts(), 'De lokale transactie heeft blijvende testgegevens achtergelaten.');
echo "Transactieterugdraaiing bevestigd; bestaande gegevens ongewijzigd.\n";
