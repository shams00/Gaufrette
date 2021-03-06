<?php

namespace Gaufrette\Adapter;

class ApcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     */
    public function shouldReadFromCache()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('exists', 'apcFetch'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));
        $adapter->expects($this->once())
            ->method('apcFetch')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue('Some content'));

        $this->assertSame('Some content', $adapter->read('foo'));
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not read the 'foo' file.
     */
    public function shouldFailWhenCannotReadFromCache()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('exists', 'apcFetch'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));
        $adapter->expects($this->once())
            ->method('apcFetch')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(false));

        $adapter->read('foo');
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     */
    public function shouldWriteToCache()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('exists', 'apcStore'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('apcStore')
            ->with($this->equalTo('foo'), $this->equalTo('Some content'))
            ->will($this->returnValue(true));

        $this->assertEquals(12, $adapter->write('foo', 'Some content'));
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not write the 'foo' file.
     */
    public function shouldFailWhenCannotWriteToCache()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('exists', 'apcStore'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('apcStore')
            ->with($this->equalTo('foo'), $this->equalTo('Some content'))
            ->will($this->returnValue(false));

        $adapter->write('foo', 'Some content');
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     */
    public function shouldCheckIfKeyExistsInCache()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('apcExists'))
            ->getMock();
        $adapter->expects($this->at(0))
            ->method('apcExists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));
        $adapter->expects($this->at(1))
            ->method('apcExists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(false));

        $this->assertTrue($adapter->exists('foo'));
        $this->assertFalse($adapter->exists('foo'));
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     */
    public function shouldGetKeysFromApc()
    {
        if (!defined('APC_ITER_NONE')) {
            define('APC_ITER_NONE', 0);
        }
        $iterator = new \ArrayIterator(array('foo' => 'foovalue', 'bar' => 'barvalue'));

        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('getCachedKeysIterator'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getCachedKeysIterator')
            ->will($this->returnValue($iterator));

        $this->assertSame(array('bar', 'foo'), $adapter->keys());
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     * @expectedException \RuntimeException
     */
    public function shouldFailWhenCannotFetchKeysFromCache()
    {
        if (!defined('APC_ITER_NONE')) {
            define('APC_ITER_NONE', 0);
        }

        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('getCachedKeysIterator'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getCachedKeysIterator')
            ->will($this->returnValue(null));

        $adapter->keys();
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     */
    public function shouldGetKeyMtimeFromCache()
    {
        if (!defined('APC_ITER_MTIME')) {
            define('APC_ITER_MTIME', 256);
        }
        $iterator = new \ArrayIterator(array('foo' => array('mtime' => 123)));

        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('getCachedKeysIterator', 'exists'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));
        $adapter->expects($this->once())
            ->method('getCachedKeysIterator')
            ->will($this->returnValue($iterator));

        $this->assertEquals(123, $adapter->mtime('foo'));
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     */
    public function shouldCalculateChecksumFromCachedContent()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('exists', 'apcFetch'))
            ->getMock();
        $adapter->expects($this->any())
            ->method('exists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));
        $adapter->expects($this->once())
            ->method('apcFetch')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue('Some content'));

        $this->assertEquals('b53227da4280f0e18270f21dd77c91d0', $adapter->checksum('foo'));
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     */
    public function shouldDeleteCache()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('exists', 'apcDelete'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));
        $adapter->expects($this->once())
            ->method('apcDelete')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));

        $adapter->delete('foo');
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     * @expectedException Gaufrette\Exception\FileNotFound
     */
    public function shouldFailWhenDeletingFileDoesNotExist()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('exists', 'apcDelete'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(false));
        $adapter->expects($this->never())
            ->method('apcDelete');

        $adapter->delete('foo');
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     * @expectedException \RuntimeException
     */
    public function shouldFailWhenCannotDeleteCache()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('exists', 'apcDelete'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));
        $adapter->expects($this->once())
            ->method('apcDelete')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(false));

        $adapter->delete('foo');
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     * @expectedException Gaufrette\Exception\UnexpectedFile
     */
    public function shouldFailWhenRenamingAndTargetFileExists()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('assertExists', 'exists'))
            ->getMock();
        $adapter->expects($this->any())
            ->method('assertExists');
        $adapter->expects($this->any())
            ->method('exists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));

        $adapter->rename('bar', 'foo');
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     */
    public function shouldRenameFiles()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('write', 'assertExists', 'exists', 'read', 'delete'))
            ->getMock();
        $adapter->expects($this->any())
            ->method('assertExists');
        $adapter->expects($this->any())
            ->method('write')
            ->with($this->equalTo('foo'), $this->equalTo('bar content'));
        $adapter->expects($this->any())
            ->method('read')
            ->with($this->equalTo('bar'))
            ->will($this->returnValue('bar content'));
        $adapter->expects($this->any())
            ->method('exists')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(false));
        $adapter->expects($this->any())
            ->method('delete')
            ->with($this->equalTo('bar'));

        $adapter->rename('bar', 'foo');
    }

    /**
     * @test
     * @covers Gaufrette\Adapter\Apc
     * @expectedException \RuntimeException
     */
    public function shouldFailWhenCannotWriteRenamingFile()
    {
        $adapter = $this->getMockBuilder('Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->setMethods(array('write', 'assertExists', 'exists', 'read', 'delete'))
            ->getMock();
        $adapter->expects($this->any())
            ->method('assertExists');
        $adapter->expects($this->any())
            ->method('write')
            ->will($this->throwException(new \Exception));
        $adapter->expects($this->never())
            ->method('delete');

        $adapter->rename('bar', 'foo');
    }
}
