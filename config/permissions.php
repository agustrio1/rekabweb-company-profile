<?php

declare(strict_types=1);

$administrator = [
    'dashboard.view',
    'content.create',
    'content.edit',
    'content.publish',
    'content.delete_own',
    'content.delete_any',
    'media.view',
    'media.upload',
    'media.delete',
    'inquiries.view',
    'inquiries.update',
    'inquiries.delete',
    'settings.manage',
    'users.manage',
];

return [
    // '*' = semua izin, termasuk system.migrations
    'super_admin' => ['*'],

    'administrator' => $administrator,

    'editor' => [
        'dashboard.view',
        'content.create',
        'content.edit',
        'content.publish',
        'content.delete_own',
        'media.view',
        'media.upload',
        'inquiries.view',
        'inquiries.update',
    ],
];