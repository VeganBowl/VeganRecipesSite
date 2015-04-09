<?php

namespace Service;

use Symfony\Component\Yaml\Parser;
use ParsedownExtra;

class ArticleParser
{
    public function parse($content)
    {
        $yamlParser = new Parser();
        $markdownParser = new ParsedownExtra();

        $split = preg_split("/[\n]*[-]{3}[\n]*/", $content, 3);

        $data = $yamlParser->parse($split[1]);
        $content = $split[2];

        return array(
            'content' 		=> $markdownParser->text($content),
            'frontMatter'	=> $data,
        );
    }
}
