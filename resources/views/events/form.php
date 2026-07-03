<?php

declare(strict_types=1);

/** @var \AEFS\Models\Event|null $event */

$event ??= null;

function old(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

?>

<div class="card shadow-sm">

    <div class="card-body">

        <div class="row g-3">

            <div class="col-12">
                <label for="titel" class="form-label">
                    Titel <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    id="titel"
                    name="titel"
                    class="form-control"
                    required
                    value="<?= htmlspecialchars(old('titel', $event?->titel ?? '')) ?>">
            </div>

            <div class="col-12">
                <label for="beschrijving" class="form-label">
                    Beschrijving
                </label>

                <textarea
                    id="beschrijving"
                    name="beschrijving"
                    rows="5"
                    class="form-control"><?= htmlspecialchars(old('beschrijving', $event?->beschrijving ?? '')) ?></textarea>
            </div>

            <div class="col-md-6">
                <label for="locatie" class="form-label">
                    Locatie
                </label>

                <input
                    type="text"
                    id="locatie"
                    name="locatie"
                    class="form-control"
                    value="<?= htmlspecialchars(old('locatie', $event?->locatie ?? '')) ?>">
            </div>

            <div class="col-md-6">
                <label for="max_deelnemers" class="form-label">
                    Maximum deelnemers
                </label>

                <input
                    type="number"
                    min="1"
                    id="max_deelnemers"
                    name="max_deelnemers"
                    class="form-control"
                    value="<?= htmlspecialchars((string) old('max_deelnemers', $event?->maxDeelnemers ?? '')) ?>">
            </div>

            <div class="col-md-6">
                <label for="startdatum" class="form-label">
                    Startdatum <span class="text-danger">*</span>
                </label>

                <input
                    type="date"
                    id="startdatum"
                    name="startdatum"
                    class="form-control"
                    required
                    value="<?= htmlspecialchars(old('startdatum', $event?->startdatum ?? '')) ?>">
            </div>

            <div class="col-md-6">
                <label for="einddatum" class="form-label">
                    Einddatum
                </label>

                <input
                    type="date"
                    id="einddatum"
                    name="einddatum"
                    class="form-control"
                    value="<?= htmlspecialchars(old('einddatum', $event?->einddatum ?? '')) ?>">
            </div>

        </div>

    </div>

</div>

<div class="mt-4 d-flex gap-2">

    <button type="submit" class="btn btn-primary">
        Opslaan
    </button>

    <a href="/events" class="btn btn-secondary">
        Annuleren
    </a>

</div>