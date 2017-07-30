<?php namespace Maduser\Minimal\Facades;

use App\Downgrade\Controllers\DowngradeController;

Router::get('downgrade', [
    'controller' => DowngradeController::class,
    'action' => 'execute'
]);