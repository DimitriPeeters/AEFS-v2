<?php



$action ??= '';

$value ??= '';

$placeholder ??= 'Zoeken...';

?>

<form

    method="get"

    action="<?= $action ?>"

>

    <input

        type="search"

        name="zoek"

        value="<?= htmlspecialchars($value) ?>"

        placeholder="<?= htmlspecialchars($placeholder) ?>"

    >

</form>