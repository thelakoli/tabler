<?php

namespace Lakoli\TablerPreset;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Laravel\Ui\Presets\Preset;
use Symfony\Component\Finder\SplFileInfo;

class TablerPreset extends Preset
{
    /**
     * Install the preset.
     *
     * @return void
     */
    public static function install()
    {
        static::updatePackages();
        static::updateStyles();
        static::updateBootstrapping();
        // static::updateWelcomePage();
        static::updateFrontendLayouts();
        // static::updatePagination();
        static::removeNodeModules();
    }

    /**
     * Install the preset with auth routes.
     *
     * @return void
     */
    public static function installAuth()
    {
        static::scaffoldController();
        static::scaffoldAuth();
        static::updatePublicAssets();
    }

    /**
     * Update the given package array.
     *
     * @param  array  $packages
     * @return array
     */
    protected static function updatePackageArray(array $packages)
    {
        // packages to add to the package.json
        $packagesToAdd = [
            "axios" => "^0.19",
            "bootstrap" => "^4.0.0",
            "cross-env" => "^7.0",
            "jquery" => "^3.2",
            "laravel-mix" => "^5.0.1",
            "lodash" => "^4.17.13",
            "resolve-url-loader" => "^3.1.0",
            "sass" => "^1.15.2",
            "sass-loader" => "^8.0.0",
            'tabler' => '^1.0.0-alpha.7',
            'sass-loader' => '^8.0.0',
            'vue' => '^2.5.17',
            'vue-template-compiler' => '^2.6.11',
        ];

        // packages to remove from the package.json
        $packagesToRemove = ['@babel/preset-react', 'react', 'react-dom'];
        return $packagesToAdd + Arr::except($packages, $packagesToRemove);
    }
    /**
     * Update the style files for the application.
     *
     * @return void
     */
    protected static function updateStyles()
    {
        tap(new Filesystem, function ($filesystem) {
            $filesystem->deleteDirectory(resource_path('sass/frontend'));
            $filesystem->delete(public_path('js/frontend.js'));
            $filesystem->delete(public_path('css/frontend.css'));

            if (!$filesystem->isDirectory($directory = resource_path('css'))) {
                $filesystem->makeDirectory($directory, 0755, true);
            }

            $filesystem->copyDirectory(__DIR__ . '/tabler-stubs/resources/sass', resource_path('sass/'));
        });
    }
    /**
     * Update the bootstrapping files.
     *
     * @return void
     */
    protected static function updateBootstrapping()
    {
        copy(__DIR__ . '/tabler-stubs/webpack.mix.js', base_path('webpack.mix.js'));

        copy(__DIR__ . '/tabler-stubs/resources/js/bootstrap.js', resource_path('js/bootstrap.js'));
    }


    /**
     * Update the default frontend layout page file.
     *
     * @return void
     */
    protected static function updateFrontendLayouts()
    {
        tap(new Filesystem, function ($filesystem) {
        $filesystem->deleteDirectory(
            resource_path('views/frontend')
        );
        if (!$filesystem->isDirectory($directory = resource_path('frontend'))) {
            $filesystem->makeDirectory($directory, 0755, true);
        }
        $filesystem->copyDirectory(__DIR__ . '/tabler-stubs/resources/views/layouts/', resource_path('views/layouts'));
        $filesystem->copyDirectory(__DIR__ . '/tabler-stubs/resources/views/frontend', resource_path('views/frontend'));
       
    });
}


    /**
     * Update the default welcome page file.
     *
     * @return void
     */
    protected static function updateWelcomePage()
    {
        (new Filesystem)->delete(
            resource_path('views/welcome.blade.php')
        );

        copy(__DIR__ . '/tabler-stubs/resources/views/welcome.blade.php', resource_path('views/welcome.blade.php'));
    }
    /**
     * Scaffold Auth controllers into project.
     *
     * @return void
     */
    protected static function scaffoldController()
    {
        if (!is_dir($directory = app_path('Http/Controllers/Auth'))) {
            mkdir($directory, 0755, true);
        }

        $filesystem = new Filesystem;

        collect($filesystem->allFiles(base_path('vendor/laravel/ui/stubs/Auth')))
            ->each(function (SplFileInfo $file) use ($filesystem) {
                $filesystem->copy(
                    $file->getPathname(),
                    app_path('Http/Controllers/Auth/' . Str::replaceLast('.stub', '.php', $file->getFilename()))
                );
            });
    }
    /**
     * Scaffold Auth views into project.
     *
     * @return void
     */
    protected static function scaffoldAuth()
    {
        file_put_contents(app_path('Http/Controllers/HomeController.php'), static::compileControllerStub());

        file_put_contents(
            base_path('routes/web.php'),
            "Auth::routes();\n\nRoute::get('/home', 'HomeController@index')->name('home');\n\n",
            FILE_APPEND
        );

        tap(new Filesystem, function ($filesystem) {
            $filesystem->copyDirectory(__DIR__ . '/tabler-stubs/resources/views', resource_path('views'));

            collect($filesystem->allFiles(base_path('vendor/laravel/ui/stubs/migrations')))
                ->each(function (SplFileInfo $file) use ($filesystem) {
                    $filesystem->copy(
                        $file->getPathname(),
                        database_path('migrations/' . $file->getFilename())
                    );
                });
        });
    }


    protected static function compileControllerStub()
    {
        return str_replace(
            '{{namespace}}',
            Container::getInstance()->getNamespace(),
            file_get_contents(__DIR__ . '/tabler-stubs/controllers/HomeController.stub')
        );
    }

    /**
     * Update the public assets.
     *
     * @return void
     */
    protected static function updatePublicAssets()
    {
        (new Filesystem)->copyDirectory(__DIR__ . '/tabler-stubs/public', public_path('static/illustrations'));
    }
}
