<?php

namespace Twig;

use Cilex\Application;

class TranslationExtension extends \Twig_Extension
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('trans', array($this, 'trans')),
        );
    }

    public function trans($message, array $params = [], $locale = null)
    {
        $locale = $locale ?: $this->app['locale'];
        $locale = $locale ?: $this->app['locale_fallbacks'];

//        $this->app['translator']->setLocale($locale);
        return $this->app['translator']->trans($message, $params, null, $locale);
    }

    public function getName()
    {
        return 'translation_extension';
    }
}
