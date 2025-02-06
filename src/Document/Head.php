<?php

declare(strict_types=1);

namespace Core\View\Document;

use Stringable;
use InvalidArgumentException;
use function String\hashKey;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Head implements Stringable
{
    /** @var array<array-key, null|array<array-key, null|bool|string>|bool|string> */
    private array $head = [];

    private array $robots = [];

    public function __construct() {}

    public function title( string $set ) : self
    {
        $this->head['title'] = $set;
        return $this;
    }

    public function description( string $set ) : self
    {
        $this->head['description'] = $set;
        return $this;
    }

    /**
     * @param string ...$add
     *
     * @return $this
     */
    public function keywords( string ...$add ) : self
    {
        // $value = \implode( ', ', (array) $value );

        foreach ( $add as $keyword ) {
            $this->robots[$keyword] = $keyword;
        }

        return $this;
    }

    public function author( string $set ) : self
    {
        $this->head['description'] = $set;
        return $this;
    }

    public function meta( ?string $name = null, null|string|bool|int|float ...$set ) : self
    {
        $meta = $name ? ['name' => $name] : [];

        foreach ( $set as $attribute => $value ) {
            if ( \is_int( $attribute ) ) {
                $message = 'Document Meta attributes must use named arguments.';
                throw new InvalidArgumentException();
            }
            $attribute        = \str_replace( [' ', '_'], '-', \trim( $attribute ) );
            $attribute        = \strtolower( (string) \preg_replace( '/(?<!^)[A-Z]/', '_$0', $attribute ) );
            $meta[$attribute] = ( \is_bool( $value ) || \is_null( $value ) ? $value : (string) $value );
        }

        if ( \array_key_exists( 'name', $meta ) ) {
            $this->head['name'] = $meta;
        }
        else {
            $this->head[] = $meta;
        }

        return $this;
    }

    protected function metaHtml( ?string $name = null, string ...$set ) : void
    {
        $key  = $name;
        $meta = '<meta';

        if ( $name ) {
            $meta .= " name=\"{$name}\"";
        }

        // if ( $content ) {
        //     $meta .= " content=\"{$content}\"";
        // }

        foreach ( $set as $property => $content ) {
            if ( \is_int( $property ) ) {
                if ( 0 === $property && ! \array_key_exists( 'content', $set ) ) {
                    $property = 'content';
                }
                else {
                    throw new InvalidArgumentException( 'Named arguments only' );
                }
            }

            $property = \str_replace( '_', '-', $property );

            $key  .= ".{$property}";
            $meta .= " {$property}=\"{$content}\"";
        }
        $meta .= '/>';

        $key = \trim( (string) $key, " \n\r\t\v\0." );

        $this->head[$key] = $meta;
    }

    // public function robots()
    // {
    //
    // }

    public function injectHtml( string|Stringable $html, ?string $key = null ) : self
    {
        $key              ??= $html instanceof Stringable ? $html::class.\spl_object_id( $html ) : hashKey( $html );
        $this->head[$key] ??= (string) $html;
        return $this;
    }

    /**
     * @return string[]
     */
    public function array() : array
    {
        return $this->head;
    }

    public function render() : string
    {
        return "<head>\n\t".\implode( "\n\t", $this->head )."\n</head>";
    }

    public function __toString() : string
    {
        // TODO : Sort before dump
        return $this->render();
    }
}
