<?php
namespace Repack\ScssPhp\Base;

/**
 * Range
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 */
class Range
{
    /**
     * @var mixed
     */
    public $first;
    /**
     * @var mixed
     */
    public $last;

    /**
     * Initialize range
     *
     * @param integer|float $first
     * @param integer|float $last
     */
    public function __construct($first, $last)
    {
        $this->first = $first;
        $this->last = $last;
    }

    /**
     * Test for inclusion in range
     *
     * @param integer|float $value
     *
     * @return boolean
     */
    public function includes($value)
    {
        return $value >= $this->first && $value <= $this->last;
    }
}
