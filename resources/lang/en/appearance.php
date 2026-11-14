<?php

declare(strict_types=1);

return [

    'page' => [
        'title' => 'Appearance',
    ],

    'mode' => [
        'heading' => 'Mode',
        'description' => 'Light, dark, or the same as your system. Saved in this browser.',
    ],

    'palettes' => [
        'heading' => 'Colour palette',
        'description' => 'Changes the navigation bar, the buttons and the background. Saved on your account, so it follows you to any device.',
        'label' => 'Palettes',
        'in_use' => 'In use',
    ],

    'custom' => [
        'heading' => 'Your palettes',
        'description' => 'Start from an existing palette and change its colours.',
        'empty' => 'You have not made a palette yet.',
        'created_by_you' => 'Made by you',
    ],

    'fields' => [
        'name' => 'Name',
        'base' => 'Start from',
        'primary' => [
            'label' => 'Main colour',
            'helper' => 'Main button, links and charts.',
        ],
        'navigation' => [
            'label' => 'Navigation bar',
            'helper' => 'The text turns white or black, whichever reads better.',
        ],
        'background' => [
            'label' => 'Background',
            'helper' => 'Behind cards and tables, in light mode.',
        ],
    ],

    'actions' => [
        'create' => [
            'label' => 'Create palette',
            'modal_heading' => 'Create palette',
            'modal_description' => 'Three colours are enough. Status colours (success, warning, danger) are the same in every palette.',
            'submit' => 'Save and use',
        ],
        'edit' => [
            'label' => 'Edit',
            'modal_heading' => 'Edit palette ":name"',
            'submit' => 'Save palette',
        ],
        'delete' => [
            'label' => 'Delete',
            'modal_heading' => 'Delete the palette ":name"?',
            'modal_description' => 'If it is the one you are using, you go back to the default palette.',
            'submit' => 'Delete palette',
        ],
    ],

    'notifications' => [
        'deleted' => 'Palette deleted',
    ],

    'breakdown' => [
        'total' => 'total',
        'others' => 'Others',
        'loading' => 'Loading the chart',
        'empty' => 'Nothing to show for this period.',
    ],

    'mobile' => [
        'shortcuts' => 'Shortcuts',
        'menu' => 'Menu',
        'more' => 'More',
        'close' => 'Close',
        'search' => 'Search the menu',
        'search_placeholder' => 'Go to…',
        'pages' => '{1} :count page|[2,*] :count pages',
    ],

];
