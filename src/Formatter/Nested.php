<?php
namespace Repack\ScssPhp\Formatter;

use Repack\ScssPhp\Type;
use Repack\ScssPhp\Formatter;
use Repack\ScssPhp\Formatter\OutputBlock;

/**
 * Nested formatter
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class Nested extends Formatter
{
    /**
     * @var integer
     */
    private $depth;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->indentLevel = 0;
        $this->indentChar = '  ';
        $this->break = "\n";
        $this->open = ' {';
        $this->close = ' }';
        $this->tagSeparator = ', ';
        $this->assignSeparator = ': ';
        $this->keepSemicolons = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function indentStr()
    {
        $n = $this->depth - 1;

        return str_repeat($this->indentChar, max($this->indentLevel + $n, 0));
    }

    /**
     * {@inheritdoc}
     */
    protected function blockLines(OutputBlock $block)
    {
        $inner = $this->indentStr();

        $glue = $this->break . $inner;

        foreach ($block->lines as $index => $line) {
            if (substr($line, 0, 2) === '/*') {
                $block->lines[$index] = preg_replace('/(\r|\n)+/', $glue, $line);
            }
        }

        $this->write($inner . implode($glue, $block->lines));
    }

    /**
     * @param $block
     */
    protected function hasFlatChild($block)
    {
        foreach ($block->children as $child) {
            if (empty($child->selectors)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function block(OutputBlock $block)
    {
        /**
         * @var mixed
         */
        static $depths;
        /**
         * @var mixed
         */
        static $downLevel;
        /**
         * @var mixed
         */
        static $closeBlock;
        /**
         * @var mixed
         */
        static $previousEmpty;
        /**
         * @var mixed
         */
        static $previousHasSelector;

        if ($block->type === 'root') {
            $depths = array(0);
            $downLevel = '';
            $closeBlock = '';
            $this->depth = 0;
            $previousEmpty = false;
            $previousHasSelector = false;
        }

        $isMediaOrDirective = in_array($block->type, array(Type::T_DIRECTIVE, Type::T_MEDIA));
        $isSupport = ($block->type === Type::T_DIRECTIVE
            && $block->selectors && strpos(implode('', $block->selectors), '@supports') !== false);

        while ($block->depth < end($depths) || ($block->depth == 1 && end($depths) == 1)) {
            array_pop($depths);
            $this->depth--;

            if (!$this->depth && ($block->depth <= 1 || (!$this->indentLevel && $block->type === Type::T_COMMENT)) &&
                (($block->selectors && !$isMediaOrDirective) || $previousHasSelector)
            ) {
                $downLevel = $this->break;
            }

            if (empty($block->lines) && empty($block->children)) {
                $previousEmpty = true;
            }
        }

        if (empty($block->lines) && empty($block->children)) {
            return;
        }

        $this->currentBlock = $block;

        if (!empty($block->lines) || (!empty($block->children) && ($this->depth < 1 || $isSupport))) {
            if ($block->depth > end($depths)) {
                if (!$previousEmpty || $this->depth < 1) {
                    $this->depth++;
                    $depths[] = $block->depth;
                } else {
                    // keep the current depth unchanged but take the block depth as a new reference for following blocks
                    array_pop($depths);
                    $depths[] = $block->depth;
                }
            }
        }

        $previousEmpty = ($block->type === Type::T_COMMENT);
        $previousHasSelector = false;

        if (!empty($block->selectors)) {
            if ($closeBlock) {
                $this->write($closeBlock);
                $closeBlock = '';
            }

            if ($downLevel) {
                $this->write($downLevel);
                $downLevel = '';
            }

            $this->blockSelectors($block);

            $this->indentLevel++;
        }

        if (!empty($block->lines)) {
            if ($closeBlock) {
                $this->write($closeBlock);
                $closeBlock = '';
            }

            if ($downLevel) {
                $this->write($downLevel);
                $downLevel = '';
            }

            $this->blockLines($block);
            $closeBlock = $this->break;
        }

        if (!empty($block->children)) {
            if ($this->depth > 0 && ($isMediaOrDirective || !$this->hasFlatChild($block))) {
                array_pop($depths);
                $this->depth--;
                $this->blockChildren($block);
                $this->depth++;
                $depths[] = $block->depth;
            } else {
                $this->blockChildren($block);
            }
        }

        // reclear to not be spoiled by children if T_DIRECTIVE
        if ($block->type === Type::T_DIRECTIVE) {
            $previousHasSelector = false;
        }

        if (!empty($block->selectors)) {
            $this->indentLevel--;

            $this->write($this->close);
            $closeBlock = $this->break;

            if ($this->depth > 1 && !empty($block->children)) {
                array_pop($depths);
                $this->depth--;
            }
            if (!$isMediaOrDirective) {
                $previousHasSelector = true;
            }
        }

        if ($block->type === 'root') {
            $this->write($this->break);
        }
    }
}
