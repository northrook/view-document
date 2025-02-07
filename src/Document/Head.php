<?php

declare(strict_types=1);

namespace Core\View\Document;

use Core\View\Html\Element;
use Stringable;
use InvalidArgumentException;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Head implements Stringable
{
    private const array SINGLE_META = ['charset', 'viewport', 'description', 'keywords', 'author'];

    protected ?string $title = null;

    protected ?string $description = null;

    protected array $keywords = [];

    protected ?string $author = null;

    /** @var string[] */
    protected array $styles = [];

    /** @var string[] */
    protected array $scripts = [];

    /** @var string[] */
    protected array $links = [];

    /** @var array<array-key, array<array-key, null|bool|string>|string> */
    private array $head = [];

    public function __construct() {}

    public function title( string $set ) : self
    {
        $this->title = $set;
        return $this;
    }

    public function description( string $set ) : self
    {
        $this->description = $set;
        return $this;
    }

    /**
     * @param string ...$add
     *
     * @return $this
     */
    public function keywords( string ...$add ) : self
    {
        // Single line, remove any HTML tags
        $string = \strip_tags( \implode( ' ', $add ) );
        // Only use word characters and whitespace
        $string = (string) \preg_replace( "/[^\w\s+$]/u", '', $string );
        // Remove unnecessary whitespace
        $keywords = (string) \preg_replace( "#\s+#", ' ', $string );

        // Explode and parse each
        foreach ( \explode( ' ', \strtolower( $keywords ) ) as $keyword ) {
            $this->keywords[$keyword] ??= $keyword;
        }

        return $this;
    }

    public function author( string $set ) : self
    {
        $this->author = $set;
        return $this;
    }

    public function meta( ?string $name = null, null|string|bool|int|float ...$set ) : self
    {
        $meta = $name ? ['name' => $name] : [];

        foreach ( $set as $attribute => $value ) {
            if ( \is_int( $attribute ) ) {
                $message = 'Document Meta attributes must use named arguments.';
                throw new InvalidArgumentException( $message );
            }
            $attribute        = \str_replace( [' ', '_'], '-', \trim( $attribute ) );
            $attribute        = \strtolower( (string) \preg_replace( '/(?<!^)[A-Z]/', '_$0', $attribute ) );
            $meta[$attribute] = ( \is_bool( $value ) || \is_null( $value ) ? $value : (string) $value );
        }

        if ( \array_key_exists( 'name', $meta ) ) {
            $this->head[$name] = $meta;
        }
        else {
            $this->head[] = $meta;
        }

        return $this;
    }

    /**
     * @param ?string                                   $href
     * @param ?string                                   $inline
     * @param null|array<array-key, string>|bool|string ...$attributes
     *
     * @return $this
     */
    public function styles(
        ?string                   $href = null,
        ?string                   $inline = null,
        string|bool|array|null ...$attributes,
    ) : self {
        if ( ! ( $href ?? $inline ) ) {
            throw new InvalidArgumentException( __METHOD__.' requires either $src or $inject.' );
        }

        $this->styles[] = Element::style( $href, $inline, ...$attributes );

        return $this;
    }

    /**
     * @param ?string                                   $src
     * @param ?string                                   $inline
     * @param null|array<array-key, string>|bool|string ...$attributes
     *
     * @return $this
     */
    public function script(
        ?string                   $src = null,
        ?string                   $inline = null,
        string|bool|array|null ...$attributes,
    ) : self {
        if ( ! ( $src ?? $inline ) ) {
            throw new InvalidArgumentException( __METHOD__.' requires either $src or $inject.' );
        }

        $this->scripts[] = Element::script( $src, $inline, ...$attributes );

        return $this;
    }

    /**
     * @param string                                    $href
     * @param null|array<array-key, string>|bool|string ...$attributes
     *
     * @return $this
     */
    public function link(
        string                    $href,
        string|bool|array|null ...$attributes,
    ) : Head {
        $this->scripts[] = Element::link( $href, ...$attributes );

        return $this;
    }

    public function injectHtml( string|Stringable $html, ?string $key = null ) : self
    {
        $key ??= $html instanceof Stringable
                ? $html::class.\spl_object_id( $html )
                : \hash( 'xxh3', $html );
        $this->head[$key] ??= (string) $html;
        return $this;
    }

    /**
     * @return array<array-key, string>
     */
    public function array() : array
    {
        $head = [
            'charset'  => '<meta charset="utf-8">',
            'viewport' => null,
            ...$this->getDocumentMeta(),
        ];

        foreach ( $this->head as $key => $meta ) {
            // Generate a valid key to prevent duplication
            if ( \is_int( $key ) ) {
                $key = \is_array( $meta ) ? \implode( '.', \array_keys( $meta ) ) : $meta;
            }

            $unique   = \in_array( $key, $this::SINGLE_META, true );
            $existing = $head[$key] ?? null;

            if ( $unique && $existing ) {
                throw new InvalidArgumentException( 'Duplicate meta key found: '.$key );
                // continue;
            }

            if ( \is_array( $meta ) ) {
                foreach ( $meta as $attribute => $value ) {
                    $meta[$attribute] = "{$attribute}=\"{$value}\"";
                }
                $meta = '<meta '.\implode( ' ', $meta ).'>';
            }

            if ( \is_string( $meta ) ) {
                $head[$key] = $meta;
            }
        }

        foreach ( [
            ...$this->styles,
            ...$this->scripts,
            ...$this->links,
        ] as $source ) {
            $head[] = $source;
        }
        return $head;
    }

    public function render() : string
    {
        return "<head>\n\t".\implode( "\n\t", $this->array() )."\n</head>";
    }

    public function __toString() : string
    {
        // TODO : Sort before dump
        return $this->render();
    }

    /**
     * @return array<string,string>
     */
    protected function getDocumentMeta() : array
    {
        $meta = [];
        if ( $this->title ) {
            $meta['title'] = "<title>{$this->title}</title>";
        }

        if ( $this->description ) {
            $meta['description'] = "<meta name=\"description\" content=\"{$this->description}\">";
        }

        if ( $this->keywords ) {
            $keywords         = \implode( ' ', $this->keywords );
            $meta['keywords'] = "<meta name=\"keywords\" content=\"{$keywords}\">";
        }

        if ( $this->author ) {
            $meta['author'] = "<meta name=\"author\" content=\"{$this->author}\">";
        }

        return $meta;
    }
}
