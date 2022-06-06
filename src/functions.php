<?php

declare(strict_types=1);

//https://github.com/cakephp/cakephp/blob/4.x/src/Core/functions.php#L41
//https://github.com/cakephp/cakephp/blob/4.x/tests/TestCase/Core/FunctionsTest.php#L61
//https://github.com/illuminate/support/blob/master/helpers.php#L101
//https://github.com/yiisoft/html/blob/master/src/Html.php#L174
if (! function_exists('e')) {
    /**
     * Convenience method for htmlspecialchars.
     *
     * @param mixed       $text    Text to wrap through htmlspecialchars. Also works with arrays, and objects.
     *             Arrays will be mapped and have all their elements escaped. Objects will be string cast if they
     *             implement a `__toString` method. Otherwise the class name will be used.
     *             Other scalar types will be returned unchanged.
     * @param bool        $double  Encode existing html entities.
     * @param string|null $charset Character set to use when escaping.
     *   Defaults to config value in `mb_internal_encoding()` or 'UTF-8'.
     *
     * @return mixed Wrapped text.
     *
     * @link https://book.cakephp.org/4/en/core-libraries/global-constants-and-functions.html#h
     */
    function e(mixed $text, bool $double = true, ?string $charset = null): mixed
    {
        if (is_string($text)) {
            //optimize for strings
        } elseif (is_array($text)) {
            $texts = [];
            foreach ($text as $k => $t) {
                $texts[$k] = h($t, $double, $charset);
            }

            return $texts;
        } elseif (is_object($text)) {
            if (method_exists($text, '__toString')) {
                $text = $text->__toString();
            } else {
                $text = '(object)' . $text::class;
            }
        } elseif ($text === null || is_scalar($text)) {
            return $text;
        }

        static $defaultCharset = false;
        if ($defaultCharset === false) {
            $defaultCharset = mb_internal_encoding() ?: 'UTF-8';
        }

        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, $charset ?: $defaultCharset, $double);
    }
}
