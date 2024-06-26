<?php

use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->artisan('modular:make-module ModuleName');
});

afterEach(function () {
    (new Filesystem)->deleteDirectory(base_path('modules'));
});

it('can run modular:make-factory command', function () {
    $this->artisan('modular:make-factory ModuleName FactoryName')->assertSuccessful();
});

it('can generate a factory', function () {
    $this->artisan('modular:make-factory ModuleName FactoryName');

    $factory = base_path('modules/ModuleName/Database/Factories/FactoryNameFactory.php');
    $this->assertTrue(file_exists($factory));

    $factoryContent = file_get_contents($factory);
    expect($factoryContent)->toContain('namespace Modules\ModuleName\Database\Factories;');
    expect($factoryContent)->toContain('class FactoryNameFactory');
});
