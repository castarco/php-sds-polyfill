<?php
declare(strict_types=1);


namespace SDS\Tests\Matrix;


use PHPUnit\Framework\TestCase;


class interfacesTests extends TestCase
{
    public function test_implements_Hashable()
    {
        $refClass = new \ReflectionClass('SDS\\Matrix');
        $this->assertTrue(in_array('Ds\\Hashable', $refClass->getInterfaceNames()));
    }

    public function test_implements_ArrayAccess()
    {
        $refClass = new \ReflectionClass('SDS\\Matrix');
        $this->assertTrue(in_array('ArrayAccess', $refClass->getInterfaceNames()));
    }

    public function test_implements_Countable()
    {
        $refClass = new \ReflectionClass('SDS\\Matrix');
        $this->assertTrue(in_array('Countable', $refClass->getInterfaceNames()));
    }

    public function test_implements_IteratorAggregate()
    {
        $refClass = new \ReflectionClass('SDS\\Matrix');
        $this->assertTrue(in_array('IteratorAggregate', $refClass->getInterfaceNames()));
    }

    public function test_implements_Traversable()
    {
        $refClass = new \ReflectionClass('SDS\\Matrix');
        $this->assertTrue(in_array('Traversable', $refClass->getInterfaceNames()));
    }
}
