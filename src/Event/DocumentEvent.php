<?php

namespace Ikwattro\ElastiGen\Event;

use Symfony\Component\EventDispatcher\Event;

class DocumentEvent extends Event
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $fields;

    /**
     * DocumentEvent constructor.
     * @param string $type
     * @param array $fields
     */
    public function __construct($type, array $fields)
    {
        $this->type = $type;
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
}