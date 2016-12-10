<?php

return
[
    // If multiple groups are assigned to user
    // and there are conflicting permissions per route
    // which logical operator to use
    //
    // Allowed values: and, or
    // Default: and
    // Behavior:
    // and: all permissions must be set to true
    // or: at least one of the permissions must be set to true
    'logic_operator' => 'and'
];
