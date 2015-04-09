<?php

namespace Twig;

use Symfony\Component\Intl\Intl;

class LanguageExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('langName', array($this, 'langName')),
        );
    }

    public function langName($language, $region = null, $displayLocale = null)
    {
        $displayLocale = $displayLocale !== null ? $displayLocale : $language;

        return ucfirst(Intl::getLanguageBundle()->getLanguageName($language, $region, $displayLocale));
    }

    public function getName()
    {
        return 'lang_name_extension';
    }
}
