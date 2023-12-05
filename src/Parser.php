<?php

declare( strict_types=1 );

namespace JazzMan\HtaccessParser;

use ArrayAccess;
use DomainException;
use Exception;
use InvalidArgumentException;
use JazzMan\HtaccessParser\Exception\SyntaxException;
use JazzMan\HtaccessParser\Token\Block;
use JazzMan\HtaccessParser\Token\Comment;
use JazzMan\HtaccessParser\Token\Directive;
use JazzMan\HtaccessParser\Token\WhiteLine;
use JetBrains\PhpStorm\Pure;
use SplFileObject;

class Parser {

    final public const IGNORE_WHITELINES = 2;

    final public const IGNORE_COMMENTS = 4;

    final public const AS_ARRAY = 8;

    protected array|ArrayAccess|null $container = null;

    /**
     * @var int Defaults to IGNORE_WHITELINES
     */
    protected int $mode = 2;

    protected bool $rewind = true;

    private int $_cpMode = 2;

    /**
     * Create a new Htaccess Parser object.
     *
     * @param SplFileObject|null $file [optional] The .htaccess file to read.
     *                                 Must be set before running the parse method
     */
    public function __construct( protected ?SplFileObject $file = null ) {}

    /**
     * Set the .htaccess file to parse.
     *
     * @return \JazzMan\Htaccess\Parser
     */
    public function setFile( SplFileObject $file ): self {
        $this->file = $file;

        return $this;
    }

    /**
     * Set the receiving container of the parsed htaccess.
     *
     * @param mixed $container Can be an array, an ArrayObject or an object that implements ArrayAccess
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     *
     * @api
     */
    public function setContainer( array|ArrayAccess|null $container = null ): self {

        $this->container = $container;

        return $this;
    }

    /**
     * If the Parser should use arrays instead of Token Objects (that implement TokenInterface).
     * Setting this to true returns a simple multidimensional array with scalars (no objects).
     * Default is false.
     *
     * @return $this
     *
     * @api
     */
    public function useArrays( bool $bool = true ): self {
        return $this->bitwiseCtrl( $bool, self::AS_ARRAY );
    }

    /**
     * If the parser should ignore whitelines (blank lines).
     *
     * @return $this
     *
     * @api
     */
    public function ignoreWhitelines( bool $bool = true ): self {
        return $this->bitwiseCtrl( $bool, self::IGNORE_WHITELINES );
    }

    /**
     * If the parser should ignore comment lines. Default is false.
     *
     * @return $this
     *
     * @api
     */
    public function ignoreComments( bool $bool = true ): self {
        return $this->bitwiseCtrl( $bool, self::IGNORE_COMMENTS );
    }

    /**
     * If the parser should rewind the .htaccess file pointer before reading. Default is true.
     *
     * @return $this
     *
     * @api
     */
    public function rewindFile( bool $bool = true ): static {
        $this->rewind = $bool;

        return $this;
    }

    /**
     * Set the parser mode. (primarily for unit tests, use individual methods instead).
     *
     * @return $this
     */
    public function setMode( int $mode ): static {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Parse a .htaccess file.
     *
     * @param SplFileObject|null $file [optional] The .htaccess file. If null is passed and the file wasn't previously
     *                                     set, it will raise an exception
     * @param int|null $optFlags [optional] Option flags
     *                                     - IGNORE_WHITELINES  [2] Ignores whitelines (default)
     *                                     - IGNORE_COMMENTS    [4] Ignores comments
     * @param bool $rewind [optional] If the file pointer should be moved to the start (default is true)
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws SyntaxException
     * @throws Exception
     *
     * @api
     */
    public function parse(
        ?SplFileObject $file = null,
        ?int $optFlags = null,
        ?bool $rewind = null
    ): array|ArrayAccess|HtaccessContainer {
        // Prepare passed options
        $file = $file ?: $this->file;
        $optFlags = $optFlags ?: $this->mode;
        $rewind = $rewind || $this->rewind;

        if ( ! $file instanceof SplFileObject ) {
            throw new Exception( '.htaccess file is not set. You must set it (with Prser::setFile) before calling parse' );
        }

        if ( ! $file->isReadable() ) {
            $path = $file->getRealPath();

            throw new Exception( sprintf('.htaccess file \'%s\'\' is not readable', $path) );
        }

        // Rewind file pointer
        if ( $rewind ) {
            $file->rewind();
        }

        // Current Parse Mode
        $this->_cpMode = $optFlags;

        // Modes
        $asArray = ( self::AS_ARRAY & $optFlags );

        // Container
        $htaccess = $asArray !== 0 ? [] : $this->container ?? new HtaccessContainer();

        // Dump file line by line into $htaccess
        while ( $file->valid() ) {
            // Get line
            $line = $file->getCurrentLine();

            // Parse Line
            $parsedLine = $this->parseLine( $line, $file );

            if ( null !== $parsedLine ) {
                $htaccess[] = $parsedLine;
            }
        }

        return $htaccess;
    }

    /**
     * Check if line is a white line.
     */
    protected function isWhiteLine( string $line ): bool {

        $line = trim( $line );

        return $line === '';
    }

    /**
     * Check if line is spanned across multiple lines.
     */
    protected function isMultiLine( string $line ): bool {
        $line = trim( $line );

        return preg_match( '/\\\\$/', $line ) > 0;
    }

    /**
     * Check if line is a comment.
     */
    protected function isComment( string $line ): bool {
        $line = trim( $line );

        return str_starts_with( $line, '#' ) > 0;
    }

    /**
     * Check if line is a directive.
     */
    protected function isDirective( string $line ): bool {
        $line = trim( $line );
        $pattern = '/^[^#\<]/';

        return preg_match( $pattern, $line ) > 0;
    }

    /**
     * Check if line is a block.
     */
    protected function isBlock( string $line ): bool {
        $line = trim( $line );

        return preg_match( '/^\<[^\/].*\>$/', $line ) > 0;
    }

    /**
     * Check if line is a Block end.
     *
     * @param string|null $blockName [optional] The block's name
     */
    protected function isBlockEnd( string $line, ?string $blockName = null ): bool {
        $line = trim( $line );
        $pattern = '/^\<\/';
        $pattern .= ( $blockName ) ?: '[^\s\>]+';
        $pattern .= '\>$/';

        return preg_match( $pattern, $line ) > 0;
    }

    /**
     * Parse a Multi Line.
     */
    protected function parseMultiLine( string $line, SplFileObject $file, array &$lineBreaks ): string {
        while ( $this->isMultiLine( $line ) && $file->valid() ) {
            $lineBreaks[] = \strlen( $line );

            $line2 = $file->getCurrentLine();

            // trim the ending slash
            $line = rtrim( $line, '\\' );
            // concatenate with next line
            $line = trim( $line.$line2 );
        }

        return $line;
    }

    /**
     * Parse a White Line.
     */
    #[Pure]
    protected function parseWhiteLine(): WhiteLine {
        return new WhiteLine();
    }

    /**
     * Parse a Comment Line.
     */
    protected function parseCommentLine( string $line, int ...$lineBreaks ): Comment {
        $comment = new Comment( $line );
        $comment->setLineBreaks( ...$lineBreaks );

        return $comment;
    }

    /**
     * Parse a Directive Line.
     *
     * @param mixed $lineBreaks
     *
     * @throws SyntaxException
     */
    protected function parseDirectiveLine( string $line, SplFileObject $file, int ...$lineBreaks ): Directive {
        $directive = new Directive();

        $args = $this->directiveRegex( $line );
        $name = array_shift( $args );

        if ( null === $name ) {
            $lineNum = $file->key();

            throw new SyntaxException( $lineNum, $line, 'Could not parse the name of the directive' );
        }

        $directive->setName( $name )
            ->setArguments( ...$args )
            ->setLineBreaks( ...$lineBreaks )
        ;

        return $directive;
    }

    /**
     * Parse a Block Line.
     *
     * @param mixed $lineBreaks
     *
     * @throws SyntaxException
     * @throws DomainException|InvalidArgumentException
     */
    protected function parseBlockLine( string $line, SplFileObject $file, int ...$lineBreaks ): Block {
        $block = new Block();

        $args = $this->blockRegex( $line );
        $name = array_shift( $args );

        if ( null === $name ) {
            $lineNum = $file->key();

            throw new SyntaxException( $lineNum, $line, 'Could not parse the name of the block' );
        }

        $block->setName( $name )
            ->setArguments( ...$args )
            ->setLineBreaks( ...$lineBreaks )
        ;

        // Now we parse the children
        $newLine = $file->getCurrentLine();

        while ( ! $this->isBlockEnd( $newLine, $name ) ) {
            $parsedLine = $this->parseLine( $newLine, $file );

            if ( null !== $parsedLine ) {
                $block->addChild( $parsedLine );
            }

            $newLine = $file->getCurrentLine();
        }

        return $block;
    }

    /**
     * @throws SyntaxException
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    private function parseLine( string $line, SplFileObject $file ): Block|Comment|Directive|WhiteLine|null {
        $ignoreWhiteLines = ( self::IGNORE_WHITELINES & $this->_cpMode );
        $ignoreComments = ( self::IGNORE_COMMENTS & $this->_cpMode );

        // Trim line
        $line = trim( $line );

        $lineBreaks = [];

        if ( $this->isMultiLine( $line ) ) {
            $line = $this->parseMultiLine( $line, $file, $lineBreaks );
        }

        if ( $this->isWhiteLine( $line ) ) {
            return ( $ignoreWhiteLines === 0 ) ? $this->parseWhiteLine() : null;
        }

        if ( $this->isComment( $line ) ) {
            return ( $ignoreComments === 0 ) ? $this->parseCommentLine( $line, ...$lineBreaks ) : null;
        }

        if ( $this->isDirective( $line ) ) {
            return $this->parseDirectiveLine( $line, $file, ...$lineBreaks );
        }

        if ( $this->isBlock( $line ) ) {
            return $this->parseBlockLine( $line, $file, ...$lineBreaks );
        }

        // Syntax not recognized so we throw SyntaxException
        throw new SyntaxException( $file->key(), $line, 'Unexpected line' );
    }

    private function bitwiseCtrl( bool $bool, int $flag ): static {
        if ( $bool ) {
            $this->mode |= $flag;
        } else {
            $this->mode &= ~$flag;
        }

        return $this;
    }

    private function directiveRegex( string $str ): array {
        $pattern = '/"(?:\\.|[^\\"])*"|\S+/';
        $matches = [];
        $trimmedMatches = [];

        if ( preg_match_all( $pattern, $str, $matches ) && isset( $matches[0] ) ) {
            foreach ( $matches[0] as $match ) {
                $match = trim( (string) $match );

                if ( '' !== $match ) {
                    $trimmedMatches[] = $match;
                }
            }

            return $trimmedMatches;
        }

        return [];
    }

    private function blockRegex( string $line ): array {
        $pattern = '/(?:[\s|<]")([^<>"]+)(?:"[\s|>])|([^<>\s]+)/';
        $final = [];

        if ( preg_match_all( $pattern, $line, $matches ) > 0 ) {
            array_walk( $matches[0], static function ( $val, $key ) use ( &$final ): void {
                if ( null !== $val ) {
                    $val = trim( (string) $val );
                    $val = trim( $val, '<>' );
                    $final[ $key ] = $val;
                }
            } );
            ksort( $final );
        }

        return $final;
    }
}
