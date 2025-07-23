<?php

use OCP\Util;
$appId = OCA\CatGifs\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-main');
Util::addStyle($appId, 'main');
?>

<div id="app-content">
    <?php
        if($_['app_version']) {
            echo '<h3>Cat Gif app version : ' . $_['app_version'] . '</h3>';
        }
    ?>

<div id="catgifs"></div>
</div>
