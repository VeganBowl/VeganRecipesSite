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

        // Twig
        $this->register(new \Cilex\Provider\TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/../app/Resources/views',
        ));

        // Translation
        $this->register(new \Cilex\Provider\TranslationServiceProvider(), array(
            'locale_fallbacks' => array('en'),
        ));

        // Load yml translation files
        $this['translator'] = $this->share($this->extend('translator', function($translator, $this) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

            $finder = new \Symfony\Component\Finder\Finder();
            $finder->files()->in( __DIR__.'/../app/Resources/translations')->name('messages.*.yml');

            foreach ($finder as $file) {
                preg_match('#messages\.([a-z]+)\.yml#', basename($file->getRealPath()), $match);

                $translator->addResource('yaml',__DIR__.'/../app/Resources/translations/'.basename($file->getRealPath()), $match[1]);
            }

            return $translator;
        }));

        // Twig extensions
        $this['twig'] = $this->share($this->extend('twig', function ($twig, $this) {
            $twig->addExtension(new Twig\LanguageExtension($this));
            $twig->addExtension(new Twig\TranslationExtension($this));
            $twig->addExtension(new Twig_Extension_Debug());

            return $twig;
        }));
    }
}
