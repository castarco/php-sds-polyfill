# PHP-SDS : Scientific Data Structures (polyfill)

**WARNING:** This repository has been archived. I stopped maintaining it long ago, when my laptop was stolen and a lot of pending work was lost.

[![Author](http://img.shields.io/badge/author-@castarco-blue.svg?style=flat-square)](https://twitter.com/castarco)
[![Build Status](https://img.shields.io/travis/SciPHPy/php-sds-polyfill/master.svg?style=flat-square)](https://travis-ci.org/SciPHPy/php-sds-polyfill)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/php-sds/polyfill.svg?style=flat-square)](https://packagist.org/packages/php-sds/polyfill)
[![Total Downloads](https://img.shields.io/packagist/dt/php-sds/polyfill.svg?style=flat-square)](https://packagist.org/packages/php-sds/polyfill)

This library is a pure and complete PHP replacement for the native SDS extension (Scientific Data Structures). This
package is slower than its native counterpart, but it's useful to ensure compatibility with systems where no extensions
can be installed.

The SDS library provides the following classes (mainly ported from Python (SciPy & Pandas)):
  * `Tensor` (abstract):
    * `IntTensor`
    * `FloatTensor`
  * `Matrix` (abstract):
    * `IntMatrix` : Not yet completed (but usable!)
    * `FloatMatrix` : Not yet completed (but usable!)
  * `DataFrame` : Not yet implemented
  * `Series` : Not yet implemented



## Usage

### How to install

To install this package in your project, type the command

```bash
composer require php-sds/polyfill
```

### Tensor

Tensors are something like a multidimensional array, or a generalization of a matrix.

**To construct them**, we have to use some helper methods:

```php
<?php

use SDS\FloatTensor;
use SDS\IntTensor;

// Example: We can represent 100 8x8 matrix
$fT = FloatTensor::zeros([100, 8, 8]);

// Example: We can represent 100 frames of 128x128px with three color channels
$iT = IntTensor::zeros([100, 3, 128, 128]);

// There are other default constructors/factories
$t = IntTensor::ones([100, 3, 128, 128]);
$t = IntTensor::constant(42, [100, 3, 128, 128]);
```

We can **access** every value contained inside a `Tensor` instance:
```php
<?php

$iT[[37, 1, 74, 25]] = 389;
$v = $iT[[37, 1, 74, 25]]; // $v === 389;
```

We can **slice** `Tensor` instances:
```php
<?php

/**
 * $t1 = 1 2 3
 *       4 5 6
 *       7 8 9
 * 
 * $t2 = 1 2
 *       4 5
 */
$t2 = $t1[[ [0, 1], [0, 1] ]];
```

We can **assign slices to sub-regions** of our `Tensor` instances:
```php
<?php

/**
 * $t1 = 0 0 0
 *       0 0 0
 *       0 0 0
 */
$t1 = \SDS\IntTensor::zeros([3, 3]);

/**
 * $t2 = 1 1
 *       1 1
 */
$t2 = \SDS\IntTensor::ones([2, 2]);

/**
 * $t1 = 1 1 0
 *       1 1 0
 *       0 0 0
 */
$t1[[ [0, 1], [0, 1] ]] = $t2;
```
