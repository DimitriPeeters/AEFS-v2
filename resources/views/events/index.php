<?php

declare(strict_types=1);

/** @var array<AEFS\Models\Event> $events */
/** @var string $zoekterm */

$title = 'Evenementen';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Evenementen</h1>

        <a href="/events/create" class="btn btn-primary">
            Nieuw evenement
        </a>
    </div>

    <form method="get" action="/events" class="row g-2 mb-4">

        <div class="col-md-8">
            <input
                type="text"
                class="form-control"
                name="q"
                value="<?= htmlspecialchars($zoekterm) ?>"
                placeholder="Zoek op titel of locatie">
        </div>

        <div class="col-md-2 d-grid">
            <button class="btn btn-secondary">
                Zoeken
            </button>
        </div>

        <div class="col-md-2 d-grid">
            <a href="/events" class="btn btn-outline-secondary">
                Wissen
            </a>
        </div>

    </form>

    <div class="card shadow-sm">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                <tr>
                    <th>Titel</th>
                    <th>Locatie</th>
                    <th>Startdatum</th>
                    <th>Einddatum</th>
                    <th class="text-end">Acties</th>
                </tr>

                </thead>

                <tbody>

                <?php if (empty($events)): ?>

                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Geen evenementen gevonden.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($events as $event): ?>

                        <tr>

                            <td><?= htmlspecialchars($event->titel) ?></td>

                            <td><?= htmlspecialchars($event->locatie ?? '-') ?></td>

                            <td><?= htmlspecialchars($event->startdatum) ?></td>

                            <td><?= htmlspecialchars($event->einddatum ?? '-') ?></td>

                            <td class="text-end">

                                <a
                                    href="/events/<?= $event->eventId ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    Bekijken
                                </a>

                                <a
                                    href="/events/<?= $event->eventId ?>/edit"
                                    class="btn btn-sm btn-outline-warning">
                                    Bewerken
                                </a>

                                <form
                                    method="post"
                                    action="/events/<?= $event->eventId ?>/delete"
                                    class="d-inline">

                                    <button
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Evenement verwijderen?')">
                                        Verwijderen
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>