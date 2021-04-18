<?php

namespace Barryvdh\LaravelIdeHelper;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Database\Eloquent\Factory;
use ReflectionClass;

class Factories
{
    public static function all()
    {
        $directories = config('ide-helper.factory_locations');
        $factories = [];
        foreach ($directories as $dir) {
            if (is_dir(base_path($dir))) {
                $dir = base_path($dir);
            }

            $dirs = glob($dir, GLOB_ONLYDIR);
            foreach ($dirs as $dir) {

                if (!is_dir($dir)) {
                    continue;
                }

                if (file_exists($dir)) {
                    $classMap = ClassMapGenerator::createMap($dir);

                    // Sort list so it's stable across different environments
                    ksort($classMap);

                    foreach ($classMap as $factory => $path) {
                        $factories[] = $factory;
                    }
                }
            }
        }

        $result = [];

        foreach ($factories as $factory) {
            $class = new ReflectionClass($factory);

            if ($parent = $class->getParentClass()) {
                if ($parent->getName() == 'Illuminate\Database\Eloquent\Factories\Factory') {
                    $result[] = new ReflectionClass($factory);
                }
            }
        }

        return $result;
    }
}
