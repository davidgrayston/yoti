<?php
/**
 * Generated by Protobuf protoc plugin.
 *
 * File descriptor : EncryptedData.proto
 */


namespace compubapi_v1;

/**
 * Protobuf message : compubapi_v1.EncryptedData
 */
class EncryptedData extends \Protobuf\AbstractMessage
{

    /**
     * @var \Protobuf\UnknownFieldSet
     */
    protected $unknownFieldSet = null;

    /**
     * @var \Protobuf\Extension\ExtensionFieldMap
     */
    protected $extensions = null;

    /**
     * iv optional bytes = 1
     *
     * @var \Protobuf\Stream
     */
    protected $iv = null;

    /**
     * cipher_text optional bytes = 2
     *
     * @var \Protobuf\Stream
     */
    protected $cipher_text = null;

    /**
     * Check if 'iv' has a value
     *
     * @return bool
     */
    public function hasIv()
    {
        return $this->iv !== null;
    }

    /**
     * Get 'iv' value
     *
     * @return \Protobuf\Stream
     */
    public function getIv()
    {
        return $this->iv;
    }

    /**
     * Set 'iv' value
     *
     * @param \Protobuf\Stream $value
     */
    public function setIv($value = null)
    {
        if ($value !== null && ! $value instanceof \Protobuf\Stream) {
            $value = \Protobuf\Stream::wrap($value);
        }

        $this->iv = $value;
    }

    /**
     * Check if 'cipher_text' has a value
     *
     * @return bool
     */
    public function hasCipherText()
    {
        return $this->cipher_text !== null;
    }

    /**
     * Get 'cipher_text' value
     *
     * @return \Protobuf\Stream
     */
    public function getCipherText()
    {
        return $this->cipher_text;
    }

    /**
     * Set 'cipher_text' value
     *
     * @param \Protobuf\Stream $value
     */
    public function setCipherText($value = null)
    {
        if ($value !== null && ! $value instanceof \Protobuf\Stream) {
            $value = \Protobuf\Stream::wrap($value);
        }

        $this->cipher_text = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function extensions()
    {
        if ( $this->extensions !== null) {
            return $this->extensions;
        }

        return $this->extensions = new \Protobuf\Extension\ExtensionFieldMap(__CLASS__);
    }

    /**
     * {@inheritdoc}
     */
    public function unknownFieldSet()
    {
        return $this->unknownFieldSet;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromStream($stream, \Protobuf\Configuration $configuration = null)
    {
        return new self($stream, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values)
    {
        $message = new self();
        $values  = array_merge([
            'iv' => null,
            'cipher_text' => null
        ], $values);

        $message->setIv($values['iv']);
        $message->setCipherText($values['cipher_text']);

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public static function descriptor()
    {
        return \google\protobuf\DescriptorProto::fromArray([
            'name'      => 'EncryptedData',
            'field'     => [
                \google\protobuf\FieldDescriptorProto::fromArray([
                    'number' => 1,
                    'name' => 'iv',
                    'type' => \google\protobuf\FieldDescriptorProto\Type::TYPE_BYTES(),
                    'label' => \google\protobuf\FieldDescriptorProto\Label::LABEL_OPTIONAL()
                ]),
                \google\protobuf\FieldDescriptorProto::fromArray([
                    'number' => 2,
                    'name' => 'cipher_text',
                    'type' => \google\protobuf\FieldDescriptorProto\Type::TYPE_BYTES(),
                    'label' => \google\protobuf\FieldDescriptorProto\Label::LABEL_OPTIONAL()
                ]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function toStream(\Protobuf\Configuration $configuration = null)
    {
        $config  = $configuration ?: \Protobuf\Configuration::getInstance();
        $context = $config->createWriteContext();
        $stream  = $context->getStream();

        $this->writeTo($context);
        $stream->seek(0);

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function writeTo(\Protobuf\WriteContext $context)
    {
        $stream      = $context->getStream();
        $writer      = $context->getWriter();
        $sizeContext = $context->getComputeSizeContext();

        if ($this->iv !== null) {
            $writer->writeVarint($stream, 10);
            $writer->writeByteStream($stream, $this->iv);
        }

        if ($this->cipher_text !== null) {
            $writer->writeVarint($stream, 18);
            $writer->writeByteStream($stream, $this->cipher_text);
        }

        if ($this->extensions !== null) {
            $this->extensions->writeTo($context);
        }

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function readFrom(\Protobuf\ReadContext $context)
    {
        $reader = $context->getReader();
        $length = $context->getLength();
        $stream = $context->getStream();

        $limit = ($length !== null)
            ? ($stream->tell() + $length)
            : null;

        while ($limit === null || $stream->tell() < $limit) {

            if ($stream->eof()) {
                break;
            }

            $key  = $reader->readVarint($stream);
            $wire = \Protobuf\WireFormat::getTagWireType($key);
            $tag  = \Protobuf\WireFormat::getTagFieldNumber($key);

            if ($stream->eof()) {
                break;
            }

            if ($tag === 1) {
                \Protobuf\WireFormat::assertWireType($wire, 12);

                $this->iv = $reader->readByteStream($stream);

                continue;
            }

            if ($tag === 2) {
                \Protobuf\WireFormat::assertWireType($wire, 12);

                $this->cipher_text = $reader->readByteStream($stream);

                continue;
            }

            $extensions = $context->getExtensionRegistry();
            $extension  = $extensions ? $extensions->findByNumber(__CLASS__, $tag) : null;

            if ($extension !== null) {
                $this->extensions()->add($extension, $extension->readFrom($context, $wire));

                continue;
            }

            if ($this->unknownFieldSet === null) {
                $this->unknownFieldSet = new \Protobuf\UnknownFieldSet();
            }

            $data    = $reader->readUnknown($stream, $wire);
            $unknown = new \Protobuf\Unknown($tag, $wire, $data);

            $this->unknownFieldSet->add($unknown);

        }
    }

    /**
     * {@inheritdoc}
     */
    public function serializedSize(\Protobuf\ComputeSizeContext $context)
    {
        $calculator = $context->getSizeCalculator();
        $size       = 0;

        if ($this->iv !== null) {
            $size += 1;
            $size += $calculator->computeByteStreamSize($this->iv);
        }

        if ($this->cipher_text !== null) {
            $size += 1;
            $size += $calculator->computeByteStreamSize($this->cipher_text);
        }

        if ($this->extensions !== null) {
            $size += $this->extensions->serializedSize($context);
        }

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->iv = null;
        $this->cipher_text = null;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(\Protobuf\Message $message)
    {
        if ( ! $message instanceof \compubapi_v1\EncryptedData) {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to %s must be a %s, %s given', __METHOD__, __CLASS__, get_class($message)));
        }

        $this->iv = ($message->iv !== null) ? $message->iv : $this->iv;
        $this->cipher_text = ($message->cipher_text !== null) ? $message->cipher_text : $this->cipher_text;
    }


}
