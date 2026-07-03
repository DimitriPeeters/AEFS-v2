<?php

declare(strict_types=1);

/**
 * Verwachte variabelen:
 *
 * $title
 * $content
 */

$title ??= 'AEFS';

?>
<!DOCTYPE html>

<html lang="nl">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars($title, ENT_QUOTES) ?> | AEFS</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{

    --primary:#1e40af;
    --primary-hover:#1d4ed8;
    --background:#f3f6fb;
    --card:#ffffff;
    --border:#e5e7eb;
    --text:#1f2937;
    --muted:#6b7280;

}

body{

    font-family:Segoe UI,Tahoma,Verdana,sans-serif;

    background:var(--background);

    color:var(--text);

}

.wrapper{

    display:flex;

    min-height:100vh;

}

.main{

    flex:1;

    display:flex;

    flex-direction:column;

    min-width:0;

}

.content{

    padding:30px;

    flex:1;

}

.page-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:25px;

}

.page-header h1{

    font-size:32px;

}

.page-header small{

    color:var(--muted);

}

.card{

    background:var(--card);

    border-radius:12px;

    box-shadow:0 10px 30px rgba(0,0,0,.05);

    padding:25px;

}

.table{

    width:100%;

    border-collapse:collapse;

}

.table th{

    background:#f8fafc;

    text-align:left;

}

.table th,
.table td{

    padding:14px;

    border-bottom:1px solid var(--border);

}

.table tr:hover{

    background:#fafafa;

}

.btn{

    display:inline-block;

    background:var(--primary);

    color:white;

    padding:10px 18px;

    border-radius:8px;

    border:none;

    text-decoration:none;

    cursor:pointer;

    transition:.2s;

}

.btn:hover{

    background:var(--primary-hover);

}

.btn-danger{

    background:#dc2626;

}

.btn-danger:hover{

    background:#b91c1c;

}

input,
select,
textarea{

    width:100%;

    padding:10px;

    border:1px solid #d1d5db;

    border-radius:8px;

    font-size:14px;

}

textarea{

    resize:vertical;

}

label{

    display:block;

    margin-bottom:6px;

    font-weight:600;

}

.grid{

    display:grid;

    gap:20px;

}

.grid-2{

    grid-template-columns:repeat(2,1fr);

}

.grid-3{

    grid-template-columns:repeat(3,1fr);

}

.grid-4{

    grid-template-columns:repeat(4,1fr);

}

@media(max-width:1100px){

    .grid-4,
    .grid-3,
    .grid-2{

        grid-template-columns:1fr;

    }

}

</style>

</head>

<body>

<div class="wrapper">

    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main">

        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">

            <?php require __DIR__ . '/../partials/flash.php'; ?>

            <?= $content ?>

        </main>

        <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>

</div>

</body>

</html>