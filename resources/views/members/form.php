<?php

declare(strict_types=1);

/** @var AEFS\Models\Member|null $lid */

?>

<div class="grid grid-2">

    <div>

        <label>Voornaam</label>

        <input
            type="text"
            name="voornaam"
            value="<?= htmlspecialchars($lid->voornaam ?? '', ENT_QUOTES) ?>"
            required
        >

    </div>

    <div>

        <label>Achternaam</label>

        <input
            type="text"
            name="achternaam"
            value="<?= htmlspecialchars($lid->achternaam ?? '', ENT_QUOTES) ?>"
            required
        >

    </div>

    <div>

        <label>E-mail</label>

        <input
            type="email"
            name="email"
            value="<?= htmlspecialchars($lid->email ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Telefoon</label>

        <input
            type="text"
            name="telefoon"
            value="<?= htmlspecialchars($lid->telefoon ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>GSM</label>

        <input
            type="text"
            name="gsm"
            value="<?= htmlspecialchars($lid->gsm ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Geboortedatum</label>

        <input
            type="date"
            name="geboortedatum"
            value="<?= htmlspecialchars($lid->geboortedatum ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Straat</label>

        <input
            type="text"
            name="straat"
            value="<?= htmlspecialchars($lid->straat ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Huisnummer</label>

        <input
            type="text"
            name="huisnummer"
            value="<?= htmlspecialchars($lid->huisnummer ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Bus</label>

        <input
            type="text"
            name="bus"
            value="<?= htmlspecialchars($lid->bus ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Postcode</label>

        <input
            type="text"
            name="postcode"
            value="<?= htmlspecialchars($lid->postcode ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Gemeente</label>

        <input
            type="text"
            name="gemeente"
            value="<?= htmlspecialchars($lid->gemeente ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Land</label>

        <input
            type="text"
            name="land"
            value="<?= htmlspecialchars($lid->land ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Geslacht</label>

        <select name="geslacht">

            <option value=""></option>

            <option value="M" <?= ($lid->geslacht ?? '') === 'M' ? 'selected' : '' ?>>Man</option>

            <option value="V" <?= ($lid->geslacht ?? '') === 'V' ? 'selected' : '' ?>>Vrouw</option>

            <option value="X" <?= ($lid->geslacht ?? '') === 'X' ? 'selected' : '' ?>>X</option>

        </select>

    </div>

    <div>

        <label>T-shirtmaat</label>

        <input
            type="text"
            name="tshirtmaat"
            value="<?= htmlspecialchars($lid->tshirtmaat ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>IBAN</label>

        <input
            type="text"
            name="rekeningnummer"
            value="<?= htmlspecialchars($lid->rekeningnummer ?? '', ENT_QUOTES) ?>"
        >

    </div>

    <div>

        <label>Rijksregisternummer</label>

        <input
            type="text"
            name="rijksregisternummer"
            value="<?= htmlspecialchars($lid->rijksregisternummer ?? '', ENT_QUOTES) ?>"
        >

    </div>

</div>

<br>

<label>Opmerkingen</label>

<textarea
    name="opmerkingen"
    rows="6"
><?= htmlspecialchars($lid->opmerkingen ?? '', ENT_QUOTES) ?></textarea>

<br><br>

<label>

    <input
        type="checkbox"
        name="actief"
        value="1"
        <?= ($lid->actief ?? true) ? 'checked' : '' ?>
    >

    Actief

</label>

<br>

<label>

    <input
        type="checkbox"
        name="gdpr_consent"
        value="1"
        <?= ($lid->gdprConsent ?? false) ? 'checked' : '' ?>
    >

    GDPR toestemming

</label>

<br><br>

<button class="btn">

    Opslaan

</button>

<style>

input,
select,
textarea{

    width:100%;

    padding:10px;

    border:1px solid #d1d5db;

    border-radius:8px;

}

label{

    display:block;

    margin-bottom:6px;

    font-weight:600;

}

textarea{

    resize:vertical;

}

</style>