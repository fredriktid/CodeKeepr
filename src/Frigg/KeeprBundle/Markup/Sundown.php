<?php

namespace Frigg\KeeprBundle\Markup;

use FOS\CommentBundle\Markup\ParserInterface;
use Sundown\Markdown;

class Sundown implements ParserInterface
{
    private $parser;

    protected function getParser()
    {
        if (null === $this->parser) {
            $this->parser = new Markdown(
                new \Sundown\Render\HTML(array('filter_html' => true)),
                array('autolink' => true)
            );
        }

        return $this->parser;
    }

    public function parse($raw)
    {
        return $this->getParser()->render($raw);
    }
}
