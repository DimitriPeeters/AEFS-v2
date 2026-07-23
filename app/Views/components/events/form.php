<?php

use AEFS\Core\View\Helper\ViewHelpers;
use App\Models\Event;

/** @var ViewHelpers $helpers */
/** @var Event|null $event */

$event ??= null;

$oldInput = $helpers->old->all();

$value = static function (
    string $key,
    mixed $default = ''
) use ($oldInput): mixed {
    return array_key_exists($key, $oldInput)
        ? $oldInput[$key]
        : $default;
};

$titel = (string) $value(
    'titel',
    $event?->titel ?? ''
);

$beschrijving = (string) $value(
    'beschrijving',
    $event?->beschrijving ?? ''
);

$locatie = (string) $value(
    'locatie',
    $event?->locatie ?? ''
);

$maxDeelnemers = $value(
    'max_deelnemers',
    $event?->maxDeelnemers ?? ''
);

$startdatum = (string) $value(
    'startdatum',
    $event?->startDatum ?? ''
);

$einddatum = (string) $value(
    'einddatum',
    $event?->eindDatum ?? ''
);

$status = (string) $value(
    'status',
    $event?->status ?? Event::STATUS_CONCEPT
);
?>

<section class="card event-form-card">
    <header class="card__header">
        <h2 class="card__title">
            Evenementgegevens
        </h2>
    </header>

    <div class="card__body">
        <div class="event-form-grid">
            <div class="form-group event-form-field--full">
                <label
                    for="titel"
                    class="form-label"
                >
                    Titel
                    <span class="event-form-required">*</span>
                </label>

                <input
                    type="text"
                    id="titel"
                    name="titel"
                    value="<?= $this->escape($titel) ?>"
                    class="form-control"
                    maxlength="255"
                    required
                    autofocus
                    autocomplete="off"
                >

                <?= $helpers->errorRenderer->field(
                    $helpers->errors,
                    'titel'
                ) ?>
            </div>

            <div class="form-group">
                <label
                    for="status"
                    class="form-label"
                >
                    Status
                    <span class="event-form-required">*</span>
                </label>

                <select
                    id="status"
                    name="status"
                    class="form-control"
                    aria-describedby="status-help"
                    required
                >
                    <?php foreach (
                        Event::statusOptions() as $optionValue => $optionLabel
                    ): ?>
                        <option
                            value="<?= $this->escape($optionValue) ?>"
                            <?= $status === $optionValue
                                ? 'selected'
                                : '' ?>
                        >
                            <?= $this->escape($optionLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <small
                    id="status-help"
                    class="event-form-help"
                >
                    Alleen concepten zijn niet zichtbaar voor gewone leden.
                </small>

                <?= $helpers->errorRenderer->field(
                    $helpers->errors,
                    'status'
                ) ?>
            </div>

            <div class="form-group">
                <label
                    for="max_deelnemers"
                    class="form-label"
                >
                    Maximum deelnemers
                </label>

                <input
                    type="number"
                    id="max_deelnemers"
                    name="max_deelnemers"
                    value="<?= $this->escape(
                        (string) $maxDeelnemers
                    ) ?>"
                    class="form-control"
                    min="1"
                    step="1"
                    inputmode="numeric"
                    aria-describedby="max-deelnemers-help"
                >

                <small
                    id="max-deelnemers-help"
                    class="event-form-help"
                >
                    Laat leeg wanneer er geen algemene deelnemerslimiet is.
                </small>

                <?= $helpers->errorRenderer->field(
                    $helpers->errors,
                    'max_deelnemers'
                ) ?>
            </div>

            <div class="form-group event-form-field--full">
                <label
                    for="beschrijving"
                    class="form-label"
                >
                    Beschrijving
                </label>

                <textarea
                    id="beschrijving"
                    name="beschrijving"
                    rows="6"
                    class="form-control event-form-textarea"
                ><?= $this->escape($beschrijving) ?></textarea>

                <?= $helpers->errorRenderer->field(
                    $helpers->errors,
                    'beschrijving'
                ) ?>
            </div>

            <div class="form-group event-form-field--full">
                <label
                    for="locatie"
                    class="form-label"
                >
                    Locatie
                </label>

                <input
                    type="text"
                    id="locatie"
                    name="locatie"
                    value="<?= $this->escape($locatie) ?>"
                    class="form-control"
                    maxlength="255"
                    autocomplete="off"
                >

                <?= $helpers->errorRenderer->field(
                    $helpers->errors,
                    'locatie'
                ) ?>
            </div>

            <div class="form-group">
                <label
                    for="startdatum"
                    class="form-label"
                >
                    Startdatum
                    <span class="event-form-required">*</span>
                </label>

                <input
                    type="date"
                    id="startdatum"
                    name="startdatum"
                    value="<?= $this->escape($startdatum) ?>"
                    class="form-control"
                    required
                >

                <?= $helpers->errorRenderer->field(
                    $helpers->errors,
                    'startdatum'
                ) ?>
            </div>

            <div class="form-group">
                <label
                    for="einddatum"
                    class="form-label"
                >
                    Einddatum
                </label>

                <input
                    type="date"
                    id="einddatum"
                    name="einddatum"
                    value="<?= $this->escape($einddatum) ?>"
                    class="form-control"
                    aria-describedby="einddatum-help"
                >

                <small
                    id="einddatum-help"
                    class="event-form-help"
                >
                    Laat leeg voor een evenement van één dag.
                </small>

                <?= $helpers->errorRenderer->field(
                    $helpers->errors,
                    'einddatum'
                ) ?>
            </div>
        </div>
    </div>

    <footer class="card__footer event-form-actions">
        <a
            href="<?= $this->escape(
                $event !== null
                    ? $helpers->url->to(
                        '/events/' . $event->eventId
                    )
                    : $helpers->url->to('/events')
            ) ?>"
            class="btn btn-secondary"
        >
            Annuleren
        </a>

        <button
            type="submit"
            class="btn btn-success"
        >
            <?= $event !== null
                ? 'Evenement opslaan'
                : 'Evenement aanmaken' ?>
        </button>
    </footer>
</section>

<style>
    .event-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.1rem 1.25rem;
    }

    .event-form-field--full {
        grid-column: 1 / -1;
    }

    .event-form-required {
        color: var(--color-primary, #b5121b);
    }

    .event-form-textarea {
        min-height: 150px;
        resize: vertical;
    }

    .event-form-help {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-text-muted, #64748b);
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .event-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    @media (max-width: 760px) {
        .event-form-grid {
            grid-template-columns: 1fr;
        }

        .event-form-field--full {
            grid-column: auto;
        }

        .event-form-actions {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        .event-form-actions .btn {
            width: 100%;
        }
    }
</style>