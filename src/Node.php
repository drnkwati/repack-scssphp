<?php
namespace Repack\ScssPhp;

/**
 * Base node
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 */
abstract class Node
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var integer
     */
    public $sourceIndex;

    /**
     * @var integer
     */
    public $sourceLine;

    /**
     * @var integer
     */
    public $sourceColumn;
}
