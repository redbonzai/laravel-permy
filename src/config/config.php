<?php

return
[
    // If multiple groups are assigned to user
    // and there are conflicting permissions per route
    // which supersedes
    //
    // Allowed values: allow, restrict
    // Default: restrict
    'order' => 'restrict',

    'users_model' => 'User'
];
