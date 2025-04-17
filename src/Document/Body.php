<?php

declare(strict_types=1);

namespace Core\View\Document;

use Core\View\Element;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Body extends Element
{
    public function __construct()
    {
        parent::__construct( tag : 'body', content : ['innerHtml' => null] );
    }

    protected function build() : void
    {
        $content = $this->content->getString();

        if ( $this->hasBodyElement( $content ) ) {
            $this->attributes->merge( Element\Attributes::extract( $content, true ) );
        }

        $this->content->set( $content );
    }

    private function hasBodyElement( string $html ) : bool
    {
        return \str_starts_with( $html, '<body' ) && \str_ends_with( $html, '</body>' );
    }
}
