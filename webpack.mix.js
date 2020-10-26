let mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Custom Mix setup
 |--------------------------------------------------------------------------
 |
 */

mix.sass('resources/sass/app.scss', 'public/css');
mix.js('resources/js/app.js', 'public/js');