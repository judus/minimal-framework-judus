<?php namespace App\Downgrade\Controllers;

use App\Downgrade\Models\Parser;
use Maduser\Minimal\Facades\Config;

class DowngradeController
{
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function execute()
    {
        $this->parser->setDir(path() . 'vendor/maduser/minimal/src/Minimal');
        $this->parser->setDestination(path('storage') . 'downgrade/Minimal/src/Minimal');
        $this->parser->execute();

        $dest = '/var/www/minimal5/vendor/maduser/minimal-php56/src/Minimal';
        $this->parser->setDestination($dest);
        $this->parser->execute();
    }
}

