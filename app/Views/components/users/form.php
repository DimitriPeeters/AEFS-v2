<?php



use AEFS\Core\View\Helper\ViewHelpers;
use AEFS\Models\User;

/**
 * @var User|null $gebruiker
 * @var array $leden
 */

$isEdit = isset($gebruiker);

?>

<div class="row">

    <div class="col-md-6">

        <?= $this->component('select', [

            'name' => 'lid_id',

            'label' => 'Lid',

            'required' => true,

            'value' => $gebruiker->lidId ?? '',

            'options' => array_reduce(

                $leden,

                static function (array $options, $lid): array {

                    $options[$lid->lidId] = $lid->fullName();

                    return $options;

                },

                [

                    '' => '-- Selecteer een lid --',

                ]

            ),

        ]) ?>

    </div>

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'email',

            'label' => 'E-mailadres',

            'type' => 'email',

            'required' => true,

            'value' => $gebruiker->email ?? '',

        ]) ?>

    </div>

</div>

<div class="row">

    <div class="col-md-6">

        <?= $this->component('input', [

            'name' => 'password',

            'label' => $isEdit
                ? 'Nieuw wachtwoord'
                : 'Wachtwoord',

            'type' => 'password',

            'required' => !$isEdit,

        ]) ?>

    </div>

    <div class="col-md-6">

        <?= $this->component('select', [

            'name' => 'rol',

            'label' => 'Rol',

            'required' => true,

            'value' => $gebruiker->rol ?? '',

            'options' => [

                User::ROLE_ADMIN => 'Administrator',

                User::ROLE_EVENTMANAGER => 'Eventmanager',

                User::ROLE_COORDINATOR => 'Coördinator',

                User::ROLE_MEMBER => 'Lid',

            ],

        ]) ?>

    </div>

</div>

<div class="row">

    <div class="col-md-4">

        <?= $this->component('checkbox', [

            'name' => 'actief',

            'label' => 'Actief',

            'checked' => $gebruiker->actief ?? true,

        ]) ?>

    </div>

    <div class="col-md-4">

        <?= $this->component('checkbox', [

            'name' => 'mail_blacklist',

            'label' => 'Mail blacklist',

            'checked' => $gebruiker->mailBlacklist ?? false,

        ]) ?>

    </div>

    <div class="col-md-4">

        <?= $this->component('checkbox', [

            'name' => 'wachtwoord_moet_wijzigen',

            'label' => 'Wachtwoord wijzigen bij volgende login',

            'checked' => $gebruiker->wachtwoordMoetWijzigen ?? false,

        ]) ?>

    </div>

</div>

<hr>

<div class="d-flex justify-content-between">

    <?= $this->component('button', [

        'href' => $helpers->url->to('/users'),

        'text' => 'Annuleren',

        'type' => 'secondary',

    ]) ?>

    <?= $this->component('button', [

        'text' => $isEdit
            ? 'Gebruiker opslaan'
            : 'Gebruiker aanmaken',

        'icon' => 'save',

        'type' => 'success',

    ]) ?>

</div>