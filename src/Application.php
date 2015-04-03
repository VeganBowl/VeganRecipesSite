<?php

class Application extends \Cilex\Application
{
    const NAME = 'VeganRecipesBuilder';
    const VERSION = '0.0.1@dev';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);
        $this['build_dir'] = __DIR__.'/../build';
    }

    public function boot()
    {
        // Commands
        $this->command(new \Command\PreviewCommand);
    }
}
