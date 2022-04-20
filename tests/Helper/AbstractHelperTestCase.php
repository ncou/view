<?php

declare(strict_types=1);

namespace Chiron\View\Tests\Helper;

use Chiron\View\TemplatePath;
use PHPUnit\Framework\TestCase;
use Chiron\View\StringTemplate;
use Chiron\View\Helper\HtmlHelper;

abstract class AbstractHelperTestCase extends TestCase
{
    /**
     * Asserts HTML tags.
     *
     * Takes an array $expected and generates a regex from it to match the provided $string.
     * Samples for $expected:
     *
     * Checks for an input tag with a name attribute (contains any non-empty value) and an id
     * attribute that contains 'my-input':
     *
     * ```
     * ['input' => ['name', 'id' => 'my-input']]
     * ```
     *
     * Checks for two p elements with some text in them:
     *
     * ```
     * [
     *   ['p' => true],
     *   'textA',
     *   '/p',
     *   ['p' => true],
     *   'textB',
     *   '/p'
     * ]
     * ```
     *
     * You can also specify a pattern expression as part of the attribute values, or the tag
     * being defined, if you prepend the value with preg: and enclose it with slashes, like so:
     *
     * ```
     * [
     *   ['input' => ['name', 'id' => 'preg:/FieldName\d+/']],
     *   'preg:/My\s+field/'
     * ]
     * ```
     *
     * Important: This function is very forgiving about whitespace and also accepts any
     * permutation of attribute order. It will also allow whitespace between specified tags.
     *
     * @param array $expected An array, see above
     * @param string $string An HTML/XHTML/XML string
     * @param bool $fullDebug Whether more verbose output should be used.
     * @return bool
     */
    public function assertHtml(array $expected, string $string, bool $fullDebug = false): bool
    {
        $regex = [];
        $normalized = [];
        foreach ($expected as $key => $val) {
            if (!is_numeric($key)) {
                $normalized[] = [$key => $val];
            } else {
                $normalized[] = $val;
            }
        }
        $i = 0;
        foreach ($normalized as $tags) {
            if (!is_array($tags)) {
                $tags = (string)$tags;
            }
            $i++;
            if (is_string($tags) && $tags[0] === '<') {
                /** @psalm-suppress InvalidArrayOffset */
                $tags = [substr($tags, 1) => []];
            } elseif (is_string($tags)) {
                $tagsTrimmed = preg_replace('/\s+/m', '', $tags);

                if (preg_match('/^\*?\//', $tags, $match) && $tagsTrimmed !== '//') {
                    $prefix = ['', ''];

                    if ($match[0] === '*/') {
                        $prefix = ['Anything, ', '.*?'];
                    }
                    $regex[] = [
                        sprintf('%sClose %s tag', $prefix[0], substr($tags, strlen($match[0]))),
                        sprintf('%s\s*<[\s]*\/[\s]*%s[\s]*>[\n\r]*', $prefix[1], substr($tags, strlen($match[0]))),
                        $i,
                    ];
                    continue;
                }
                if (!empty($tags) && preg_match('/^preg\:\/(.+)\/$/i', $tags, $matches)) {
                    $tags = $matches[1];
                    $type = 'Regex matches';
                } else {
                    $tags = '\s*' . preg_quote($tags, '/');
                    $type = 'Text equals';
                }
                $regex[] = [
                    sprintf('%s "%s"', $type, $tags),
                    $tags,
                    $i,
                ];
                continue;
            }
            foreach ($tags as $tag => $attributes) {
                /** @psalm-suppress PossiblyFalseArgument */
                $regex[] = [
                    sprintf('Open %s tag', $tag),
                    sprintf('[\s]*<%s', preg_quote($tag, '/')),
                    $i,
                ];
                if ($attributes === true) {
                    $attributes = [];
                }
                $attrs = [];
                $explanations = [];
                $i = 1;
                foreach ($attributes as $attr => $val) {
                    if (is_numeric($attr) && preg_match('/^preg\:\/(.+)\/$/i', (string)$val, $matches)) {
                        $attrs[] = $matches[1];
                        $explanations[] = sprintf('Regex "%s" matches', $matches[1]);
                        continue;
                    }
                    $val = (string)$val;

                    $quotes = '["\']';
                    if (is_numeric($attr)) {
                        $attr = $val;
                        $val = '.+?';
                        $explanations[] = sprintf('Attribute "%s" present', $attr);
                    } elseif (!empty($val) && preg_match('/^preg\:\/(.+)\/$/i', $val, $matches)) {
                        $val = str_replace(
                            ['.*', '.+'],
                            ['.*?', '.+?'],
                            $matches[1]
                        );
                        $quotes = $val !== $matches[1] ? '["\']' : '["\']?';

                        $explanations[] = sprintf('Attribute "%s" matches "%s"', $attr, $val);
                    } else {
                        $explanations[] = sprintf('Attribute "%s" == "%s"', $attr, $val);
                        $val = preg_quote($val, '/');
                    }
                    $attrs[] = '[\s]+' . preg_quote($attr, '/') . '=' . $quotes . $val . $quotes;
                    $i++;
                }
                if ($attrs) {
                    $regex[] = [
                        'explains' => $explanations,
                        'attrs' => $attrs,
                    ];
                }
                /** @psalm-suppress PossiblyFalseArgument */
                $regex[] = [
                    sprintf('End %s tag', $tag),
                    '[\s]*\/?[\s]*>[\n\r]*',
                    $i,
                ];
            }
        }
        /**
         * @var array<string, mixed> $assertion
         */
        foreach ($regex as $i => $assertion) {
            $matches = false;
            if (isset($assertion['attrs'])) {
                $string = $this->_assertAttributes($assertion, $string, $fullDebug, $regex);
                if ($fullDebug === true && $string === false) {
                    debug($string, true);
                    debug($regex, true);
                }
                continue;
            }

            // If 'attrs' is not present then the array is just a regular int-offset one
            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [$description, $expressions, $itemNum] = $assertion;
            $expression = '';
            foreach ((array)$expressions as $expression) {
                $expression = sprintf('/^%s/s', $expression);
                if (preg_match($expression, $string, $match)) {
                    $matches = true;
                    $string = substr($string, strlen($match[0]));
                    break;
                }
            }
            if (!$matches) {
                if ($fullDebug === true) {
                    debug($string);
                    debug($regex);
                }
                $this->assertMatchesRegularExpression(
                    $expression,
                    $string,
                    sprintf('Item #%d / regex #%d failed: %s', $itemNum, $i, $description)
                );

                return false;
            }
        }

        $this->assertTrue(true, '%s');

        return true;
    }

    /**
     * Check the attributes as part of an assertTags() check.
     *
     * @param array<string, mixed> $assertions Assertions to run.
     * @param string $string The HTML string to check.
     * @param bool $fullDebug Whether more verbose output should be used.
     * @param array|string $regex Full regexp from `assertHtml`
     * @return string|false
     */
    protected function _assertAttributes(array $assertions, string $string, bool $fullDebug = false, $regex = '')
    {
        $asserts = $assertions['attrs'];
        $explains = $assertions['explains'];
        do {
            $matches = false;
            $j = null;
            foreach ($asserts as $j => $assert) {
                if (preg_match(sprintf('/^%s/s', $assert), $string, $match)) {
                    $matches = true;
                    $string = substr($string, strlen($match[0]));
                    array_splice($asserts, $j, 1);
                    array_splice($explains, $j, 1);
                    break;
                }
            }
            if ($matches === false) {
                if ($fullDebug === true) {
                    debug($string);
                    debug($regex);
                }
                $this->assertTrue(false, 'Attribute did not match. Was expecting ' . $explains[$j]);
            }
            $len = count($asserts);
        } while ($len > 0);

        return $string;
    }
}
