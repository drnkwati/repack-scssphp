<?php
namespace Repack\ScssPhp\Formatter;

use Repack\ScssPhp\Formatter;
use Repack\ScssPhp\Formatter\OutputBlock;

/**
 * Crunched formatter
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 */
class Crunched extends Formatter
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->indentLevel = 0;
        $this->indentChar = '  ';
        $this->break = '';
        $this->open = '{';
        $this->close = '}';
        $this->tagSeparator = ',';
        $this->assignSeparator = ':';
        $this->keepSemicolons = false;
    }

    /**
     * {@inheritdoc}
     */
    public function blockLines(OutputBlock $block)
    {
        $inner = $this->indentStr();

        $glue = $this->break . $inner;

        foreach ($block->lines as $index => $line) {
            if (substr($line, 0, 2) === '/*') {
                unset($block->lines[$index]);
            }
        }

        $this->write($inner . implode($glue, $block->lines));

        if (!empty($block->children)) {
            $this->write($this->break);
        }
    }

    /**
     * Output block selectors
     *
     * @param \Repack\ScssPhp\Formatter\OutputBlock $block
     */
    protected function blockSelectors(OutputBlock $block)
    {
        $inner = $this->indentStr();

        $this->write(
            $inner
            . implode(
                $this->tagSeparator,
                str_replace(array(' > ', ' + ', ' ~ '), array('>', '+', '~'), $block->selectors)
            )
            . $this->open . $this->break
        );
    }
}
