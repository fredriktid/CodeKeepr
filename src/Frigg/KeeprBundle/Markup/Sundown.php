<?php

namespace Frigg\KeeprBundle\Markup;

use FOS\CommentBundle\Markup\ParserInterface;
use Sundown\Markdown;
use Sundown\Render\HTML;

/**
 * Class Sundown.
 */
class Sundown implements ParserInterface
{
    /**
     * @var
     */
    private $parser;

    /**
     * @return Markdown
     */
    protected function getParser()
    {
        if (null === $this->parser) {
            $this->parser = new Markdown(
                new HTML([
                    'filter_html' => true,
                    ],
                    [
                        'autolink' => true,
                    ]
                )
            );
        }

        return $this->parser;
    }

    /**
     * @param string $raw
     *
     * @return mixed
     */
    public function parse($raw)
    {
        return $this->getParser()->render($raw);
    }
}
