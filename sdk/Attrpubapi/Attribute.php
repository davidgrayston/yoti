<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: Attribute.proto

namespace Attrpubapi;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>attrpubapi_v1.Attribute</code>
 */
class Attribute extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string name = 1;</code>
     */
    private $name = '';
    /**
     * Generated from protobuf field <code>bytes value = 2;</code>
     */
    private $value = '';
    /**
     * Generated from protobuf field <code>.attrpubapi_v1.ContentType content_type = 3;</code>
     */
    private $content_type = 0;
    /**
     * Generated from protobuf field <code>repeated .attrpubapi_v1.Anchor anchors = 4;</code>
     */
    private $anchors;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *     @type string $value
     *     @type int $content_type
     *     @type \Attrpubapi\Anchor[]|\Google\Protobuf\Internal\RepeatedField $anchors
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Attribute::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>bytes value = 2;</code>
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Generated from protobuf field <code>bytes value = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setValue($var)
    {
        GPBUtil::checkString($var, False);
        $this->value = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.attrpubapi_v1.ContentType content_type = 3;</code>
     * @return int
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * Generated from protobuf field <code>.attrpubapi_v1.ContentType content_type = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setContentType($var)
    {
        GPBUtil::checkEnum($var, \Attrpubapi\ContentType::class);
        $this->content_type = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated .attrpubapi_v1.Anchor anchors = 4;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getAnchors()
    {
        return $this->anchors;
    }

    /**
     * Generated from protobuf field <code>repeated .attrpubapi_v1.Anchor anchors = 4;</code>
     * @param \Attrpubapi\Anchor[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setAnchors($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Attrpubapi\Anchor::class);
        $this->anchors = $arr;

        return $this;
    }

}

