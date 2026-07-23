<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var AEFS\Models\User[] $gebruikers */

$gebruikers ??= [];

?>

<?php if (empty($gebruikers)): ?>

    <?= $this->component('empty-state', [

        'title' => 'Geen gebruikers gevonden',

        'text'  => 'Er zijn momenteel geen gebruikers beschikbaar.'

    ]) ?>

<?php else: ?>

<table class="table table-hover align-middle">

    <thead>

    <tr>

        <th>Naam</th>

        <th>E-mail</th>

        <th>Rol</th>

        <th class="text-center">Status</th>

        <th>Laatste login</th>

        <th class="text-end" style="width:220px;">Acties</th>

    </tr>

    </thead>

    <tbody>

    <?php foreach ($gebruikers as $gebruiker): ?>

        <tr>

            <td>

                <strong>

                    <?= htmlspecialchars($gebruiker->fullName()) ?>

                </strong>

            </td>

            <td>

                <?= htmlspecialchars($gebruiker->email) ?>

            </td>

            <td>

                <span class="badge bg-primary">

                    <?= htmlspecialchars($gebruiker->roleLabel()) ?>

                </span>

            </td>

            <td class="text-center">

                <?php if ($gebruiker->isActive()): ?>

                    <span class="badge bg-success">

                        Actief

                    </span>

                <?php else: ?>

                    <span class="badge bg-danger">

                        Inactief

                    </span>

                <?php endif; ?>

            </td>

            <td>

                <?= $gebruiker->laatsteLogin
                    ? date('d/m/Y H:i', strtotime($gebruiker->laatsteLogin))
                    : '-' ?>

            </td>

            <td class="text-end">

                <?= $this->component('button', [

                    'href' => $helpers->url->to('/users/' . $gebruiker->gebruikerId),

                    'icon' => 'eye',

                    'size' => 'sm',

                    'type' => 'secondary',

                    'title' => 'Bekijken'

                ]) ?>

                <?= $this->component('button', [

                    'href' => $helpers->url->to('/users/' . $gebruiker->gebruikerId . '/edit'),

                    'icon' => 'edit',

                    'size' => 'sm',

                    'type' => 'warning',

                    'title' => 'Wijzigen'

                ]) ?>

                <?= $this->component('button', [

                    'href' => $helpers->url->to('/users/' . $gebruiker->gebruikerId . '/delete'),

                    'icon' => 'trash',

                    'size' => 'sm',

                    'type' => 'danger',

                    'confirm' => 'Deze gebruiker verwijderen?'

                ]) ?>

            </td>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>

<?php endif; ?>