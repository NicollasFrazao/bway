const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .sass('node_modules/bootstrap/scss/bootstrap.scss', 'public/dist/css/bootstrap.css')
    .js('resources/js/app.js', 'public/dist/js')
    .js('node_modules/jquery/dist/jquery.min.js', 'public/dist/js')
    .js('node_modules/bootstrap/dist/js/bootstrap.bundle.js', 'public/dist/js/bootstrap.js')
    .copy('node_modules/bootstrap/dist/js/bootstrap.bundle.js.map', 'public/dist/js');
