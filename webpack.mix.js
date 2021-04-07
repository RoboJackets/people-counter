const mix = require('laravel-mix');

const SentryWebpackPlugin = require("@sentry/webpack-plugin");

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

if (process.env.MIX_APP_ENV === 'production') {
    mix.webpackConfig({
        devtool: 'hidden-source-map',
        plugins: [
            new SentryWebpackPlugin({
                // sentry-cli configuration
                authToken: process.env.MIX_SENTRY_AUTH_TOKEN,
                org: process.env.MIX_SENTRY_ORG_NAME,
                project: process.env.MIX_SENTRY_PROJECT_ID,

                // webpack specific configuration
                include: "public/js",
                ignore: ["node_modules", "webpack.mix.js"],
            }),
        ]
    }).sourceMaps(false, 'hidden-source-map')
} else {
    mix.webpackConfig({
        devtool: 'source-map'
    }).sourceMaps()
}

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .js(['resources/js/nova.js'], 'public/js/nova.js')
