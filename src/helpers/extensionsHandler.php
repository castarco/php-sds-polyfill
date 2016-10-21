<?php


/**
 * This is a dirty workaround around a PHP-DS related issue: https://github.com/php-ds/extension/issues/2
 */
if (!\extension_loaded('ds') && \ini_get('enable_dl') && \function_exists('\\dl') && !\PHP_ZTS) {
    if (\strtoupper(\substr(PHP_OS, 0, 3)) === 'WIN') {
        \dl('ds.dll');
    } else {
        \dl('ds.so');
    }
}
