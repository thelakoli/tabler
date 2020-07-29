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

mix.js('resources/js/frontend.js', 'public/js')
  .sass('resources/sass/frontend.scss', 'public/css')
  .extract(['apexcharts','autosize','flatpickr','fullcalendar','imask','jqvmap','nouislider','peity','selectize']);

if (mix.inProduction()) {
  mix.version();
}
