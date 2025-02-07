<?php

declare(strict_types=1);

namespace Core\View;

use Core\View\Template\View;
use Psr\Log\LoggerInterface;

// This is where we format the output HTML

class DocumentView extends View
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

    final public function setInnerHtml( string $content ) : DocumentView
    {
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
        $html = \implode( PHP_EOL, \array_filter( $html ) );

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $html = \str_replace( '><', ">\n<", $html );

        return $html;
    }
}
