    <?php

    namespace Pina;

    App::walkClasses('Installation', function($cl) {
        $cl->prepare();
    });

    $upgrades = App::getUpgrades();
    App::db()->batch($upgrades);

    if (!empty($upgrades) && is_array($upgrades)) {
        echo join($upgrades, "\r\n")."\r\n";
    }

    App::walkClasses('Installation', function($cl) {
        $cl->install();
    });
