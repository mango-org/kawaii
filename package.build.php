<?php

use packager\Event;

/**
 * @param Event $event
 * @jppm-task publish
 */
function task_publish(Event $event) {
    foreach ($event->package()->getAny('modules', []) as $i => $module)
        Tasks::runExternal("./$module", 'publish', [], "yes");
}
