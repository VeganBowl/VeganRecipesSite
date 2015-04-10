<?php

class Application extends \Cilex\Application
{
    const NAME = 'VeganRecipesBuilder';
    const VERSION = '0.0.1@dev';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);
        $this['build_dir'] = __DIR__.'/../build';
        $this['recipes_dir'] = __DIR__.'/../recipes/recipes';
    }

    public function boot()
    {
        // Commands
        $this->command(new \Command\BuildCommand);

        // Services
        $this['article_parser'] = function() {
            return new \Service\ArticleParser();
        };

        $this->register(new \Cilex\Provider\TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/../app/Resources/views',
        ));

        $this['twig'] = $this->share($this->extend('twig', function ($twig, $this) {
            $twig->addGlobal('base_url', 'http://localhost:8000');
            $twig->addExtension(new Twig\LanguageExtension($this));
            $twig->addExtension(new Twig_Extension_Debug());

            return $twig;
        }));
    }
}
