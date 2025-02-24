<?php

declare(strict_types=1);

namespace Core\View;

use Core\Interface\View;
use Psr\Log\LoggerInterface;
use Stringable;

class DocumentEngine extends View
{
    protected bool $contentOnly = false;

    public function __construct(
        public readonly Document            $document,
        protected readonly ?LoggerInterface $logger = null,
    ) {}

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
     * `<body ..>` attributes will be merged with `$this->document->body->`{@see Attributes}.
     *
     * @param string|Stringable $content
     *
     * @return $this
     */
    final public function setInnerHtml( string|Stringable $content ) : DocumentEngine
    {
        // TODO : [mid] extract and merge merge body attributes
        $this->document->body->content->set( 'innerHtml', $content );
        return $this;
    }

    final public function renderDocument() : string
    {
        return $this->render(
            '<!DOCTYPE html>',
            "<html{$this->document->html}>",
            $this->document->head->render(),
            $this->document->body->render(),
            '</html>',
        );
    }

    final public function renderContent() : string
    {
        return $this->render(
            $this->document->head->render(),
            $this->document->body->render(),
        );
    }

    final protected function render( string ...$html ) : string
    {
        return \implode( PHP_EOL, \array_filter( $html ) );
    }
}
