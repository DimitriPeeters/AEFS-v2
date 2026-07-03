<?php

declare(strict_types=1);

/** @var AEFS\Models\User|null $gebruiker */

?>

<div class="grid grid-2">

    <div>

        <label>E-mailadres</label>

        <input
            type="email"
            name="email"
            value="<?= htmlspecialchars($gebruiker->email ?? '', ENT_QUOTES) ?>"
            required
        >

    </div>

    <div>

        <label>Rol</label>

        <select name="rol" required>

            <option value="">-- Kies een rol --</option>

            <option value="admin" <?= ($gebruiker->rol ?? '') === 'admin' ? 'selected' : '' ?>>
                Administrator
            </option>

            <option value="beheerder" <?= ($gebruiker->rol ?? '') === 'beheerder' ? 'selected' : '' ?>>
                Beheerder
            </option>

            <option value="vrijwilliger" <?= ($gebruiker->rol ?? '') === 'vrijwilliger' ? 'selected' : '' ?>>
                Vrijwilliger
            </option>

        </select>

    </div>

    <div>

        <label>Lid ID</label>

        <input
            type="number"
            name="lid_id"
            value="<?= htmlspecialchars((string)($gebruiker->lidId ?? ''), ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Nieuw wachtwoord</label>

        <input
            type="password"
            name="password"
        >

    </div>

</div>

<br>

<label>

    <input
        type="checkbox"
        name="actief"
        value="1"
        <?= ($gebruiker->actief ?? true) ? 'checked' : '' ?>
    >

    Actief

</label>

<br>

<label>

    <input
        type="checkbox"
        name="mail_blacklist"
        value="1"
        <?= ($gebruiker->mailBlacklist ?? false) ? 'checked' : '' ?>
    >

    Geen e-mails ontvangen

</label>

<br>

<label>

    <input
        type="checkbox"
        name="wachtwoord_moet_wijzigen"
        value="1"
        <?= ($gebruiker->wachtwoordMoetWijzigen ?? false) ? 'checked' : '' ?>
    >

    Wachtwoord wijzigen bij volgende login

</label>

<br><br>

<button
    class="btn"
    type="submit"
>

    Opslaan

</button>