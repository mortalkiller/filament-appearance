<?php

declare(strict_types=1);

return [

    /*
     * The model that picks a palette. It must use the HasPalette trait.
     * Null uses the model of the default auth provider.
     */
    'user_model' => null,

    /*
     * The column on the user table that holds the chosen palette: the id of a palette registered
     * on the plugin, or "custom:{id}" for one the user made. Null means the default palette.
     */
    'palette_column' => 'palette',

    /*
     * The table that holds the palettes users make for themselves.
     */
    'custom_palettes_table' => 'user_palettes',

];
