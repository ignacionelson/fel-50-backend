<?php

return [
    'roles' => [
        'admin' => [
            'name' => 'Administrator',
            'description' => 'Full system access',
            'capabilities' => [
                'user.create',
                'user.read',
                'user.update',
                'user.delete',
                'user.manage_roles',
                'content.create',
                'content.read',
                'content.update',
                'content.delete',
                'system.config',
                'system.reports',
                'exhibition.manage'
            ]
        ],
        'expositor' => [
            'name' => 'Expositor',
            'description' => 'Exhibition management and content creation',
            'capabilities' => [
                'content.create',
                'content.read',
                'content.update',
                'exhibition.participate',
                'profile.manage'
            ]
        ],
        'visitante' => [
            'name' => 'Visitante',
            'description' => 'Basic visitor access',
            'capabilities' => [
                'content.read',
                'profile.view',
                'exhibition.view'
            ]
        ],
        'profesional' => [
            'name' => 'Profesional',
            'description' => 'Professional networking and content access',
            'capabilities' => [
                'content.read',
                'content.create',
                'profile.manage',
                'network.connect',
                'exhibition.view',
                'reports.basic'
            ]
        ],
        'prensa' => [
            'name' => 'Prensa',
            'description' => 'Press and media access',
            'capabilities' => [
                'content.read',
                'content.create',
                'media.access',
                'profile.manage',
                'exhibition.view',
                'reports.media'
            ]
        ]
    ],

    'capabilities' => [
        'user.create' => 'Create new users',
        'user.read' => 'View user information',
        'user.update' => 'Update user information',
        'user.delete' => 'Delete users',
        'user.manage_roles' => 'Assign and manage user roles',
        
        'content.create' => 'Create content',
        'content.read' => 'Read content',
        'content.update' => 'Update content',
        'content.delete' => 'Delete content',
        
        'profile.view' => 'View own profile',
        'profile.manage' => 'Manage own profile',
        
        'exhibition.view' => 'View exhibitions',
        'exhibition.participate' => 'Participate in exhibitions',
        'exhibition.manage' => 'Manage exhibitions',
        
        'network.connect' => 'Connect with other professionals',
        
        'media.access' => 'Access media resources',
        
        'system.config' => 'System configuration',
        'system.reports' => 'Full system reports',
        
        'reports.basic' => 'Basic reports',
        'reports.media' => 'Media reports'
    ]
];