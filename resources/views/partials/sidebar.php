<?php

declare(strict_types=1);

use AEFS\Core\Url;

?>

<aside class="sidebar">

    <div class="logo">

        <h2>AEFS</h2>

        <small>v2</small>

    </div>

    <nav>

        <a href="<?= Url::to('/dashboard') ?>">
            🏠 Dashboard
        </a>

        <a href="<?= Url::to('/leden') ?>">
            👥 Leden
        </a>

        <a href="<?= Url::to('/gebruikers') ?>">
            👤 Gebruikers
        </a>

        <a href="<?= Url::to('/evenementen') ?>">
            📅 Evenementen
        </a>

        <a href="<?= Url::to('/shiften') ?>">
            🕒 Shiftplanning
        </a>

        <a href="<?= Url::to('/inschrijvingen') ?>">
            ✅ Inschrijvingen
        </a>

        <a href="<?= Url::to('/mailings') ?>">
            📧 Mailings
        </a>

        <a href="<?= Url::to('/rapporten') ?>">
            📊 Rapporten
        </a>

        <a href="<?= Url::to('/instellingen') ?>">
            ⚙️ Instellingen
        </a>

    </nav>

    <div class="sidebar-footer">

        <form method="post" action="<?= Url::to('/logout') ?>">

            <button type="submit">

                🚪 Uitloggen

            </button>

        </form>

    </div>

</aside>

<style>

.sidebar{

    width:260px;

    min-height:100vh;

    background:#1e3a8a;

    color:white;

    display:flex;

    flex-direction:column;

}

.logo{

    padding:30px;

    border-bottom:1px solid rgba(255,255,255,.15);

}

.logo h2{

    margin:0;

    font-size:30px;

}

.logo small{

    opacity:.8;

}

.sidebar nav{

    display:flex;

    flex-direction:column;

    padding:20px 0;

    flex:1;

}

.sidebar nav a{

    color:white;

    text-decoration:none;

    padding:15px 30px;

    transition:.2s;

}

.sidebar nav a:hover{

    background:rgba(255,255,255,.12);

}

.sidebar-footer{

    padding:20px;

    border-top:1px solid rgba(255,255,255,.15);

}

.sidebar-footer button{

    width:100%;

    background:#dc2626;

    color:white;

    border:none;

    border-radius:8px;

    padding:12px;

    cursor:pointer;

}

.sidebar-footer button:hover{

    background:#b91c1c;

}

</style>