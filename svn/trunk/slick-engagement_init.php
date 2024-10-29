<?php

function SlickEngagement_init($file): void {

    require_once 'SlickEngagement_ActionsFilters.php';
    $slickActionsFilters = new SlickEngagement_ActionsFilters();

    // NOTE: this file gets run each time you *activate* the plugin.
    // So in WP when you "install" the plugin, all that does it dump its files in the plugin-templates directory
    // but it does not call any of its code.
    // So here, the plugin tracks whether or not it has run its install operation, and we ensure it is run only once
    // on the first activation
    if (!$slickActionsFilters->isInstalled()) {
        $slickActionsFilters->install();
    } else {
        // Perform any version-upgrade activities prior to activation (e.g. database changes)
        $slickActionsFilters->upgrade();
    }

    // Add callbacks to hooks
    $slickActionsFilters->addActionsAndFilters();

    if (!$file) {
        $file = __FILE__;
    }

    //register_activation_hook($file, [&$slickEngagementPlugin, 'activate']);
    //register_deactivation_hook($file, [&$slickEngagementPlugin, 'deactivate']);
}
