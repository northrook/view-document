<?php

declare(strict_types=1);

namespace Core\View;

use Core\Autowire\{Logger, Profiler};
use Core\Interface\View;
use Stringable;

class DocumentEngine extends View
{
    use Profiler, Logger;

    protected bool $contentOnly = false;

    public function __construct( public readonly Document $document ) {}

    final public function __toString() : string
    {
        return $this->contentOnly ? $this->renderContent() : $this->renderDocument();
    }

    /**
     * Determine  method is used when {@see getHtml} or {@see __toString} is called:
     * - `true` {@see renderContent}
     * - `false` {@see renderDocument}
     *
     * @param bool $set
     *
     * @return $this
     */
    final public function contentOnly( bool $set = true ) : self
    {
        $this->contentOnly = $set;

        return $this;
    }

    /**
     * Assign the `innerHtml` content.
     *
     * `<body ...>` attributes will be merged with `$this->document->body->`{@see Attributes}.
     *
     * @param string|Stringable $content
     *
     * @return $this
     */
    final public function setInnerHtml( string|Stringable $content ) : DocumentEngine
    {
        // TODO : [mid] extract and merge merge body attributes
        $this->document->body->content->set( $content, 'innerHtml' );
        return $this;
    }

    final public function renderDocument() : string
    {
        $this->profilerStart( 'render.document' );
        $document = $this->render(
            '<!DOCTYPE html>',
            "<html{$this->document->html}>",
            $this->document->head->render(),
            $this->document->body->render(),
            '</html>',
        );
        $this->profilerStop( 'render.document' );
        return $document;
    }

    final public function renderContent() : string
    {
        $this->profilerStart( 'render.content' );
        $content = $this->render(
            $this->document->head->render(),
            $this->document->body->render(),
        );
        $this->profilerStop( 'render.content' );
        return $content;
    }

    final protected function render( string ...$html ) : string
    {
        return \implode( PHP_EOL, \array_filter( $html ) );
    }
}
