<?php

return [
    'groups' => [
        'Posts' => [
            'posts.create'   => 'Write and submit posts',
            'posts.view_all' => "See every author's posts",
            'posts.edit_any' => 'Edit or trash any post',
            'posts.approve'  => 'Approve or reject posts (their own posts publish instantly)',
            'posts.trash'    => 'Manage the Trash (restore or delete forever)',
        ],
        'Comments' => [
            'comments.moderate' => 'Approve, mark as spam or delete comments',
        ],
        'Site' => [
            'categories.manage' => 'Manage categories',
            'tags.manage'       => 'Manage tags',
            'users.manage'      => 'Manage users and assign roles',
            'settings.manage'   => 'Change mail (SMTP) settings',
        ],
    ],

    'sensitive' => ['settings.manage', 'users.manage'],
    'activity.view' => 'View the activity log',
];