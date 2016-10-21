<?php
declare(strict_types=1);


namespace SDS\Tests;


use PHPUnit\Framework\TestCase;


class ClassDefinitionsTests extends TestCase
{
    public function test_Tensor_existence()
    {
        $this->assertTrue(class_exists('SDS\\Tensor'));
    }

    public function test_IntTensor_existence()
    {
        $this->assertTrue(class_exists('SDS\\IntTensor'));
    }

    public function test_FloatTensor_existence()
    {
        $this->assertTrue(class_exists('SDS\\FloatTensor'));
    }

    public function test_Matrix_existence()
    {
        $this->assertTrue(class_exists('SDS\\Matrix'));
    }

    public function test_IntMatrix_existence()
    {
        $this->assertTrue(class_exists('SDS\\IntMatrix'));
    }

    public function test_FloatMatrix_existence()
    {
        $this->assertTrue(class_exists('SDS\\FloatMatrix'));
    }

    public function test_DataFrame_existence()
    {
        $this->assertTrue(class_exists('SDS\\DataFrame'));
    }

    public function test_Series_existence()
    {
        $this->assertTrue(class_exists('SDS\\Series'));
    }
}
