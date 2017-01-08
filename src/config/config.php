<?php

return [
    // If multiple permissions are assigned to a user
    // and there are conflicting permissions per route
    // which logical operator to use
    //
    // Allowed values: and, or, xor
    // Default: and
    // Behavior:
    // and: all permissions must be true
    // or: at least one of the permissions must be true
    // xor: https://en.wikipedia.org/wiki/Exclusive_or
    'logic_operator' => 'and',

    // Users model
    // used in:
    // the CLI artisan command (permy:can)
    // the PermyModel for describing the many-to-many relation with the User
    'users_model' => 'App\User',

    // When set to true, all route permissions return true
    // useful for debugging
    'godmode' => false,

    // When set to true, all exceptions during permission checking will be thrown
    'debug' => false,

    // Available filters
    //
    // An array of filters based on which Permy builds a list of permissions to manage
    // The fillable array represents the filters that are manageable through the UI
    // The guarded array represents the filters that are not seen in the UI and are managed manually
    //
    // Default filters array:
    // 'fillable' => ['permy'],
    // 'guarded' => [],
    'filters' => [
        'fillable' => ['permy'],
        'guarded' => []
    ],
];
