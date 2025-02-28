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
        parent::__construct( 'body', ['innerHtml' => null] );
    }

    protected function build( string $separator = '' ) : string
    {
        if ( $this->tag->isSelfClosing() ) {
            return $this->tag->getOpeningTag( $this->attributes );
        }

        $body = \implode( '', $this->content->getArray() );

        if ( $this->hasBodyElement( $body ) ) {
            $this->attributes->merge( Element\Attributes::extract( $body, true ) );
        }

        return $this->tag->getOpeningTag( $this->attributes ).$body.$this->tag->getClosingTag();
    }

    private function hasBodyElement( string $html ) : bool
    {
        return \str_starts_with( $html, '<body' ) && \str_ends_with( $html, '</body>' );
    }
}
