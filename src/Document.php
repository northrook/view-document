<?php

declare(strict_types=1);

namespace Core\View;

use Core\View\Document\{Body, Head, Robots};
use Core\View\Element\Attributes;
use Core\Interface\ActionInterface;
use Psr\Log\LoggerInterface;
use Stringable;
use Core\Exception\NotSupportedException;

final class Document implements ActionInterface
{
    /** @var bool automatically locked when read. */
    private bool $locked = false;

    /** @var string[] `asset.key` format */
    protected array $enqueueAsset = [];

    public readonly Attributes $html;

    public readonly Body $body;

    public readonly Head $head;

    protected readonly Robots $robots;

    /** @var bool Determines how indexing will be handled */
    public bool $isPublic = false;

    public function __construct( protected readonly ?LoggerInterface $logger = null )
    {
        $this->html = new Attributes( ['lang' => 'en'] );
        $this->head = new Head();
        $this->body = new Body();
    }

    /**
     * @param null|string          $title
     * @param null|string          $description
     * @param null|string|string[] $keywords
     * @param null|string          $author
     * @param null|string          $status
     *
     * @return $this
     */
    public function __invoke(
        ?string           $title = null,
        ?string           $description = null,
        null|string|array $keywords = null,
        ?string           $author = null,
        ?string           $status = null,
    ) : self {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }

        foreach ( \get_defined_vars() as $name => $value ) {
            if ( ! $value ) {
                continue;
            }

            match ( $name ) {
                'title'       => $this->title( $value ),
                'description' => $this->description( $value ),
                'keywords'    => $this->keywords( ...(array) $value ),
                'author'      => $this->author( $value ),
                'status'      => $this->html->set( 'status', $status ),
                default       => $this,
            };
        }

        return $this;
    }

    /**
     * Add `attributes` to the `document.html` element.
     *
     * @param null|string                               $class
     * @param null|'animating'|'init'|'loading'|'ready' $status
     * @param null|string                               $id
     * @param string                                    $lang
     * @param string                                    ...$attributes
     *
     * @return $this
     */
    public function html(
        ?string            $class = null,
        ?string            $status = null,
        ?string            $id = null,
        string             $lang = 'en',
        bool|int|string ...$attributes,
    ) : self {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }

        $this->html->add(
            [
                'id'     => $id,
                'class'  => $class,
                'status' => $status,
                'lang'   => $lang,
                ...$attributes,
            ],
        );

        return $this;
    }

    /**
     * Add arbitrary HTML to the `document.head`.
     *
     * @param string|Stringable $html
     *
     * @return $this
     */
    public function head( string|Stringable $html ) : self
    {
        $this->head->injectHtml( $html );
        return $this;
    }

    public function title( string $set ) : self
    {
        $this->head->title( $set );
        return $this;
    }

    public function description( string $set ) : self
    {
        $this->head->description( $set );
        return $this;
    }

    public function keywords( string ...$set ) : self
    {
        $this->head->keywords( ...$set );
        return $this;
    }

    public function author( string $set ) : self
    {
        $this->head->author( $set );
        return $this;
    }

    /**
     * @param null|string                $name
     * @param null|bool|float|int|string ...$set
     *
     * @return $this
     */
    public function meta( ?string $name = null, null|string|bool|int|float ...$set ) : self
    {
        $this->head->meta( $name, ...$set );
        return $this;
    }

    public function robots( string $set ) : self
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }

        $this->robots ??= new Robots();

        // throw new NotSupportedException( 'TODO: '.__METHOD__ );
        return $this;
    }

    /**
     * @param ?string                                   $href
     * @param ?string                                   $inline
     * @param null|array<array-key, string>|bool|string ...$attributes
     *
     * @return self
     */
    public function styles(
        ?string                   $href = null,
        ?string                   $inline = null,
        string|bool|array|null ...$attributes,
    ) : self {
        $this->head->styles( $href, $inline, ...$attributes );

        return $this;
    }

    /**
     * @param ?string                                   $src
     * @param ?string                                   $inline
     * @param null|array<array-key, string>|bool|string ...$attributes
     *
     * @return self
     */
    public function script(
        ?string                   $src = null,
        ?string                   $inline = null,
        string|bool|array|null ...$attributes,
    ) : self {
        $this->head->script( $src, $inline, ...$attributes );
        return $this;
    }

    /**
     * @param string                                    $href
     * @param null|array<array-key, string>|bool|string ...$attributes
     *
     * @return self
     */
    public function link( string $href, string|bool|array|null ...$attributes ) : self
    {
        $this->head->link( $href, ...$attributes );

        return $this;
    }

    public function assets( string ...$enqueue ) : self
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }

        foreach ( $enqueue as $asset ) {
            $this->enqueueAsset[$asset] ??= $asset;
        }

        return $this;
    }

    public function theme( string $set ) : self
    {
        throw new NotSupportedException( 'TODO: '.__METHOD__ );
        // return $this;
    }

    public function body(
        ?string            $id = null,
        ?string            $class = null,
        bool|int|string ...$attributes,
    ) : self {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }

        $this->body->attributes->add(
            [
                'id'    => $id,
                'class' => $class,
                ...$attributes,
            ],
        );

        return $this;
    }

    private function isLocked( string $method = __CLASS__ ) : bool
    {
        if ( ! $this->locked ) {
            return false;
        }

        $this->logger?->error(
            'The {caller} is locked. No further changes can be made at this time.',
            ['caller' => $method, 'document' => $this],
        );

        return true;
    }
}
