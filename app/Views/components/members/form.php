<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var \AEFS\Models\Member|null $lid */

?>

<div class="row">

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'voornaam',

            'label' => 'Voornaam',

            'required' => true,

            'value' => $lid->voornaam ?? ''

        ]) ?>

    </div>

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'achternaam',

            'label' => 'Achternaam',

            'required' => true,

            'value' => $lid->achternaam ?? ''

        ]) ?>

    </div>

</div>

<div class="row">

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'email',

            'label' => 'E-mail',

            'type' => 'email',

            'value' => $lid->email ?? ''

        ]) ?>

    </div>

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'telefoon',

            'label' => 'Telefoon',

            'value' => $lid->telefoon ?? ''

        ]) ?>

    </div>

</div>

<div class="row">

    <div class="col-md-8">

        <?= $this->component('input', [

            'name' => 'straat',

            'label' => 'Straat',

            'value' => $lid->straat ?? ''

        ]) ?>

    </div>

    <div class="col-md-4">

        <?= $this->component('input', [

            'name' => 'postcode',

            'label' => 'Postcode',

            'value' => $lid->postcode ?? ''

        ]) ?>

    </div>

</div>

<div class="row">

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'gemeente',

            'label' => 'Gemeente',

            'value' => $lid->gemeente ?? ''

        ]) ?>

    </div>

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'land',

            'label' => 'Land',

            'value' => $lid->land ?? 'België'

        ]) ?>

    </div>

</div>

<div class="row">

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'geboortedatum',

            'label' => 'Geboortedatum',

            'type' => 'date',

            'value' => $lid->geboortedatum ?? ''

        ]) ?>

    </div>

    <div class="col-md-6">

        <?= $this->component('select', [

            'name' => 'geslacht',

            'label' => 'Geslacht',

            'value' => $lid->geslacht ?? '',

            'options' => [

                '' => '-- Selecteer --',

                'M' => 'Man',

                'V' => 'Vrouw',

                'X' => 'X'

            ]

        ]) ?>

    </div>

</div>

<div class="row">

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'rekeningnummer',

            'label' => 'IBAN',

            'value' => $lid->rekeningnummer ?? ''

        ]) ?>

    </div>

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'rijksregisternummer',

            'label' => 'Rijksregisternummer',

            'value' => $lid->rijksregisternummer ?? ''

        ]) ?>

    </div>

</div>

<div class="row">

    <div class="col-md-6">

        <?= $this->component('select', [

            'name' => 'tshirtmaat',

            'label' => 'T-shirtmaat',

            'value' => $lid->tshirtmaat ?? '',

            'options' => [

                '' => '-- Selecteer --',

                'XS' => 'XS',

                'S' => 'S',

                'M' => 'M',

                'L' => 'L',

                'XL' => 'XL',

                'XXL' => 'XXL',

                '3XL' => '3XL'

            ]

        ]) ?>

    </div>

    <div class="col-md-6 d-flex align-items-end">

        <?= $this->component('checkbox', [

            'name' => 'actief',

            'label' => 'Actief lid',

            'checked' => $lid->actief ?? true

        ]) ?>

    </div>

</div>

<?= $this->component('textarea', [

    'name' => 'opmerkingen',

    'label' => 'Opmerkingen',

    'rows' => 5,

    'value' => $lid->opmerkingen ?? ''

]) ?>

<?= $this->component('checkbox', [

    'name' => 'gdpr_consent',

    'label' => 'GDPR toestemming',

    'checked' => $lid->gdprConsent ?? false

]) ?>

<hr>

<div class="d-flex justify-content-between">

    <?= $this->component('button', [

        'href' => $helpers->url->to('/members'),

        'text' => 'Annuleren',

        'type' => 'secondary'

    ]) ?>

    <?= $this->component('button', [

        'text' => isset($lid)
            ? 'Lid opslaan'
            : 'Lid aanmaken',

        'icon' => 'save',

        'type' => 'success'

    ]) ?>

</div>