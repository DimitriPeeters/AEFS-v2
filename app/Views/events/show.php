<?php

use AEFS\Core\View\Helper\ViewHelpers;
use App\Models\Event;

/** @var ViewHelpers $helpers */
/** @var Event $event */
/** @var bool|null $isAdmin */
/** @var string|null $title */

$isAdmin ??= false;

$this->extend(
    'layouts.app',
    [
        'title' => $title ?? $event->titel,
    ]
);

$actions = '';

if ($isAdmin) {
    $actions = sprintf(
        '<a href="%s" class="btn btn-warning">Wijzigen</a>',
        $this->escape(
            $helpers->url->to(
                '/events/' . $event->eventId . '/edit'
            )
        )
    );
}
?>

<?php $this->startSection('content'); ?>
<div class="event-show-page">
    <?= $this->component(
        'page-header',
        [
            'title' => $event->titel,
            'subtitle' => $event->displayDate(),
            'actions' => $actions,
        ]
    ) ?>

    <?php if ($event->isCancelled()): ?>
        <div class="alert alert--error" role="alert">
            Dit evenement werd geannuleerd.
        </div>
    <?php endif; ?>

    <div class="event-show-grid">
        <section class="card">
            <header class="card__header">
                <h2 class="card__title">Evenementgegevens</h2>
            </header>

            <div class="card__body">
                <dl class="event-details">
                    <div>
                        <dt>Periode</dt>
                        <dd><?= $this->escape($event->displayDate()) ?></dd>
                    </div>

                    <div>
                        <dt>Duur</dt>
                        <dd><?= $event->durationDays() ?> dag(en)</dd>
                    </div>

                    <div>
                        <dt>Locatie</dt>
                        <dd><?= $this->escape($event->locatie ?? '-') ?></dd>
                    </div>

                    <div>
                        <dt>Capaciteit</dt>
                        <dd><?= $this->escape($event->capacityLabel()) ?></dd>
                    </div>
                </dl>

                <div class="event-description">
                    <h3>Beschrijving</h3>

                    <?php if ($event->hasDescription()): ?>
                        <p><?= nl2br($this->escape((string) $event->beschrijving)) ?></p>
                    <?php else: ?>
                        <p class="event-muted">Geen beschrijving opgegeven.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <aside class="card">
            <header class="card__header">
                <h2 class="card__title">Status</h2>
            </header>

            <div class="card__body">
                <dl class="event-details event-details--compact">
                    <div>
                        <dt>Periode</dt>
                        <dd>
                            <span class="badge <?= $this->escape($event->periodStatusCssClass()) ?>">
                                <?= $this->escape($event->periodStatusLabel()) ?>
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt>Evenementstatus</dt>
                        <dd>
                            <span class="badge <?= $this->escape($event->statusCssClass()) ?>">
                                <?= $this->escape($event->statusLabel()) ?>
                            </span>
                        </dd>
                    </div>

                    <?php if ($isAdmin): ?>
                        <div>
                            <dt>Inschrijvingen</dt>
                            <dd><?= $event->aantalInschrijvingen ?></dd>
                        </div>

                        <div>
                            <dt>Bevestigd</dt>
                            <dd><?= $event->aantalBevestigd ?></dd>
                        </div>

                        <div>
                            <dt>Planning</dt>
                            <dd><?= $this->escape($event->displayPlanningSentAt()) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </aside>
    </div>

    <div class="event-show-actions">
        <a
            href="<?= $this->escape($helpers->url->to('/events')) ?>"
            class="btn btn-secondary"
        >
            Terug naar evenementen
        </a>

        <?php if ($isAdmin): ?>
            <form
                method="post"
                action="<?= $this->escape(
                    $helpers->url->to(
                        '/events/' . $event->eventId . '/delete'
                    )
                ) ?>"
                onsubmit="return confirm('Dit evenement definitief verwijderen?');"
            >
                <?= $helpers->csrf->field() ?>

                <button type="submit" class="btn btn-danger">
                    Verwijderen
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->startSection('styles'); ?>
<style>
    .event-show-page {
        display: grid;
        gap: 1.25rem;
    }

    .event-show-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
        gap: 1.25rem;
        align-items: start;
    }

    .event-details {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin: 0;
    }

    .event-details--compact {
        grid-template-columns: 1fr;
    }

    .event-details div {
        padding: 0.85rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: #f8fafc;
    }

    .event-details dt {
        margin-bottom: 0.3rem;
        color: var(--text-muted);
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .event-details dd {
        margin: 0;
        font-weight: 600;
    }

    .event-description {
        margin-top: 1.5rem;
    }

    .event-description h3 {
        margin-bottom: 0.65rem;
        font-size: 1rem;
    }

    .event-description p {
        margin: 0;
    }

    .event-muted {
        color: var(--text-muted);
    }

    .event-show-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    @media (max-width: 900px) {
        .event-show-grid,
        .event-details {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 620px) {
        .event-show-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .event-show-actions .btn,
        .event-show-actions form {
            width: 100%;
        }
    }
</style>
<?php $this->endSection(); ?>
