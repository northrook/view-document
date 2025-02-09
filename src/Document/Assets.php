<?php

namespace Core\View\Document;

use Core\View\Element;
use InvalidArgumentException;

final class Assets
{
    /** @var array<string, bool> */
    private array $enqueued = [];

    /** @var string[] */
    protected array $styles = [];

    /** @var string[] */
    protected array $scripts = [];

    /** @var string[] */
    protected array $links = [];

    public function enqueue( string ...$assets ) : self
    {
        foreach ( $assets as $asset ) {
            $this->enqueued[$asset] ??= true;
        }

        return $this;
    }

    public function setEnqueued( string $key, bool $set ) : void
    {
        $this->enqueued[$key] = $set;
    }

    /**
     * @return string[]
     */
    public function getEnqueuedAssets() : array
    {
        return \array_keys( \array_filter( $this->enqueued ) );
    }

    /**
     * @return string[];
     */
    public function getResolvedAssets() : array
    {
        $assets = [];

        foreach ( [
            ...$this->styles,
            ...$this->scripts,
            ...$this->links,
        ] as $source ) {
            \assert( \is_string( $source ) );
            $assets[] = $source;
        }

        return $assets;
    }

    /**
     * @param ?string                                   $href
     * @param ?string                                   $inline
     * @param null|array<array-key, string>|bool|string ...$attributes
     *
     * @return $this
     */
    public function addStyle(
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
    public function addScript(
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
    public function addLink(
        string                    $href,
        string|bool|array|null ...$attributes,
    ) : self {
        $this->scripts[] = Element::link( $href, ...$attributes );

        return $this;
    }
}
