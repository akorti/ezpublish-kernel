<?php
/**
 * File containing the MediaTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Media\Type as MediaType,
    eZ\Publish\Core\Repository\FieldType\Media\Value as MediaValue,
    eZ\Publish\Core\Repository\FieldType\Media\Handler as MediaHandler,
    ezp\Io\FileInfo,
    ezp\Base\BinaryRepository,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class MediaTest extends PHPUnit_Framework_TestCase
{
    /**
     * Path to test media
     * @var string
     */
    protected $mediaPath;

    /**
     * FileInfo object for test image
     * @var \ezp\Io\FileInfo
     */
    protected $mediaFileInfo;

    protected function setUp()
    {
        parent::setUp();
        BinaryRepository::setOverrideOptions( 'inmemory' );
        $this->mediaPath = __DIR__ . '/developer-got-hurt.m4v';
        $this->mediaFileInfo = new FileInfo( $this->mediaPath );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testMediaSupportedValidators()
    {
        $ft = new MediaType;
        self::assertSame(
            array( 'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\FileSizeValidator' ),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedSettings
     */
    public function testMediaAllowedSettings()
    {
        $ft = new MediaType;
        self::assertSame(
            array( 'mediaType' ),
            $ft->allowedSettings(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::acceptValue
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @group fieldType
     * @group ezmedia
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new MediaType;
        $invalidValue = new MediaValue;
        $invalidValue->file = 'This is definitely not a binary file !';
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::acceptValue
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     * @group fieldType
     * @group ezmedia
     */
    public function testAcceptInvalidValue()
    {
        $ft = new MediaType;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' ) );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new MediaType;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $handler = new MediaHandler;
        $value = new MediaValue;
        $value->file = $handler->createFromLocalPath( $this->mediaPath );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::getHandler
     */
    public function testValueGetHandler()
    {
        $value = new MediaValue;
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\Media\\Handler', $value->getHandler() );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $value = MediaValue::fromString( $this->mediaPath );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\Media\\Value', $value );
        self::assertInstanceOf( 'ezp\\Io\\BinaryFile', $value->file );
        self::assertSame( $this->mediaFileInfo->getBasename(), $value->originalFilename );
        self::assertSame( $value->originalFilename, $value->file->originalFile );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::__toString
     */
    public function testFieldValueToString()
    {
        $value = MediaValue::fromString( $this->mediaPath );
        self::assertSame( $value->file->path, (string)$value );
    }

    /**
     * Tests legacy properties, not directly accessible from Value object
     *
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::__get
     */
    public function testVirtualLegacyProperty()
    {
        $value = MediaValue::fromString( $this->mediaPath );
        self::assertSame( basename( $value->file->path ), $value->filename );
        self::assertSame( $value->file->contentType->__toString(), $value->mimeType );
        self::assertSame( $value->file->contentType->type, $value->mimeTypeCategory );
        self::assertSame( $value->file->contentType->subType, $value->mimeTypePart );
        self::assertSame( $value->file->path, $value->filepath );
        self::assertSame( $value->file->size, $value->filesize );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Value::__get
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testInvalidVirtualProperty()
    {
        $value = MediaValue::fromString( $this->mediaPath );
        $value->nonExistingProperty;
    }
}
