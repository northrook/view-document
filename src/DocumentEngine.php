<?php

declare(strict_types=1);

namespace Core\View;

use Core\Interface\View;
use Core\Profiler\Interface\Profilable;
use Core\Profiler\StopwatchProfiler;
use Symfony\Component\Stopwatch\Stopwatch;
use Psr\Log\{
    LoggerAwareInterface,
    LoggerInterface,
};
use Stringable;

class DocumentEngine extends View implements Profilable, LoggerAwareInterface
{
    use StopwatchProfiler;

    protected readonly ?LoggerInterface $logger;

    protected bool $contentOnly = false;

    public function __construct( public readonly Document $document ) {}

    final public function setLogger( ?LoggerInterface $logger ) : void
    {
        $this->logger ??= $logger;
    }

    final public function setProfiler(
        ?Stopwatch $stopwatch,
        ?string    $category = 'Document',
    ) : void {
        $this->assignProfiler( $stopwatch, $category );
    }

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
        $this->document->body->content->set( $content, 'innerHtml' );
        return $this;
    }

    final public function renderDocument() : string
    {
        $profiler = $this->profiler?->event( 'render' );
        $document = $this->render(
            '<!DOCTYPE html>',
            "<html{$this->document->html}>",
            $this->document->head->render(),
            $this->document->body->render(),
            '</html>',
        );
        $profiler?->stop();
        return $document;
    }

    final public function renderContent() : string
    {
        $profiler = $this->profiler?->event( 'render.content' );
        $content  = $this->render(
            $this->document->head->render(),
            $this->document->body->render(),
        );
        $profiler?->stop();
        return $content;
    }

    final protected function render( string ...$html ) : string
    {
        return \implode( PHP_EOL, \array_filter( $html ) );
    }
}
