<?php

declare(strict_types=1);

namespace Chiron\View;

use RuntimeException;

// TODO : mettre cette classe à la racine du package view, et pas dans le repertoire Helper !!!!

// TODO : on deux méthode séparée dans cette classe, une pour formater une string template, l'autre pour formatter des attributs html. Il faudrait surement faire 2 classes séparées !!!! dans une classe on ajouterai la méthode "format()" et dans l'autre classe "formatAttribute" ca serai plus propre !!!!

/**
 * Provides an interface for registering and inserting
 * content into simple logic-less string templates.
 *
 * Used by several helpers to provide simple flexible templates
 * for generating HTML and other content.
 */
// TODO : remplacer les protected par des private car la classe est final
final class StringTemplate
{
    /**
     * List of attributes that can be made compact.
     *
     * @var array<string, bool>
     */
    protected array $compactAttributes = [
        'allowfullscreen' => true,
        'async'           => true,
        'autofocus'       => true,
        'autoplay'        => true,
        'checked'         => true,
        'compact'         => true,
        'controls'        => true,
        'declare'         => true,
        'default'         => true,
        'defaultchecked'  => true,
        'defaultmuted'    => true,
        'defaultselected' => true,
        'defer'           => true,
        'disabled'        => true,
        'enabled'         => true,
        'formnovalidate'  => true,
        'hidden'          => true,
        'indeterminate'   => true,
        'inert'           => true,
        'ismap'           => true,
        'itemscope'       => true,
        'loop'            => true,
        'multiple'        => true,
        'muted'           => true,
        'nohref'          => true,
        'noresize'        => true,
        'noshade'         => true,
        'novalidate'      => true,
        'nowrap'          => true,
        'open'            => true,
        'pauseonexit'     => true,
        'readonly'        => true,
        'required'        => true,
        'reversed'        => true,
        'scoped'          => true,
        'seamless'        => true,
        'selected'        => true,
        'sortable'        => true,
        'truespeed'       => true,
        'typemustmatch'   => true,
        'visible'         => true,
    ];

    /**
     * Contains the list of compiled templates
     *
     * @var array<string, array>
     */
    protected array $compiled = [];

    /**
     * Registers a list of templates by name
     *
     * ### Example:
     *
     * ```
     * $templates = ([
     *   'link'   => '<a href="{{url}}">{{title}}</a>'
     *   'button' => '<button>{{text}}</button>',
     *   'meta'   => '<meta{{attrs}}/>',
     * ]);
     * ```
     *
     * @param array<string, string> $templates An associative list of named templates.
     */
    public function __construct(array $templates = [])
    {
        $this->compileTemplates($templates);
    }

    /**
     * Compile templates into a more efficient printf() compatible format.
     *
     * @param array<string> $templates The template names to compile. If empty all templates will be compiled.
     */
    protected function compileTemplates(array $templates): void
    {
        foreach ($templates as $name => $template) {
            $template = str_replace('%', '%%', $template);
            preg_match_all('#\{\{([\w\._]+)\}\}#', $template, $matches);
            $this->compiled[$name] = [
                str_replace($matches[0], '%s', $template),
                $matches[1],
            ];
        }
    }

    /**
     * Format a template string with $data
     *
     * @param string $name The template name.
     * @param array<string, mixed> $data The data to insert.
     *
     * @return string Formatted string
     *
     * @throws \RuntimeException If template not found.
     */
    // TODO : renommer en formatTemplate()
    public function format(string $name, array $data): string
    {
        if (! isset($this->compiled[$name])) {
            throw new RuntimeException("Cannot find template named '$name'.");
        }

        [$template, $placeholders] = $this->compiled[$name];

        if (isset($data['templateVars'])) {
            $data += $data['templateVars'];
            unset($data['templateVars']);
        }

        $replace = [];
        foreach ($placeholders as $placeholder) {
            $replacement = $data[$placeholder] ?? null;
            if (is_array($replacement)) {
                $replacement = implode('', $replacement);
            }
            $replace[] = $replacement;
        }

        return vsprintf($template, $replace);
    }

    /**
     * Returns a space-delimited string with items of the $options array. If a key
     * of $options array happens to be one of those listed
     * in `StringTemplate::$_compactAttributes` and its value is one of:
     *
     * - '1' (string)
     * - 1 (integer)
     * - true (boolean)
     * - 'true' (string)
     *
     * Then the value will be reset to be identical with key's name.
     * If the value is not one of these 4, the parameter is not output.
     *
     * 'escape' is a special option in that it controls the conversion of
     * attributes to their HTML-entity encoded equivalents. Set to false to disable HTML-encoding.
     *
     * If value for any option key is set to `null` or `false`, that option will be excluded from output.
     *
     * This method uses the 'attribute' and 'compactAttribute' templates. Each of
     * these templates uses the `name` and `value` variables. You can modify these
     * templates to change how attributes are formatted.
     *
     * @param array<string, mixed>|null $options Array of options.
     * @param array<string>|null $exclude Array of options to be excluded, the options here will not be part of the return.
     *
     * @return string Composed attributes.
     */
    // TODO : vérifier pourquoi on peut passer null pour les options ???? il faudrait virer le paramétre $exclude qui ne sert pas souvent !!! et il faudrait ajouter un paramétre $escape = true par défault
    public function formatAttributes(?array $options, ?array $exclude = null): string
    {
        $insertBefore = ' ';
        $options = (array) $options + ['escape' => true];

        if (! is_array($exclude)) {
            $exclude = [];
        }

        // TODO : cette liste d'esclude ne sert pas à grand chose !!!!
        $exclude = ['escape' => true, 'idPrefix' => true, 'templateVars' => true, 'fieldName' => true]
            + array_flip($exclude);
        $escape = $options['escape'];
        $attributes = [];

        foreach ($options as $key => $value) {
            if (! isset($exclude[$key]) && $value !== false && $value !== null) {
                $attributes[] = $this->formatAttribute((string) $key, $value, $escape);
            }
        }
        $out = trim(implode(' ', $attributes));

        return $out ? $insertBefore . $out : '';
    }

    /**
     * Formats an individual attribute, and returns the string value of the composed attribute.
     * Works with minimized attributes that have the same value as their name such as 'disabled' and 'checked'
     *
     * @param string $key    The name of the attribute to create
     * @param mixed  $value  The value of the attribute to create.
     * @param bool   $escape Define if the value must be escaped
     *
     * @return string The composed attribute.
     */
    protected function formatAttribute(string $key, mixed $value, bool $escape = true): string
    {
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (is_numeric($key)) {
            return "$value=\"$value\"";
        }
        $truthy = [1, '1', true, 'true', $key];
        $isMinimized = isset($this->compactAttributes[$key]);
        if (! preg_match('/\A(\w|[.-])+\z/', $key)) {
            $key = e($key);
        }
        if ($isMinimized && in_array($value, $truthy, true)) {
            return "$key=\"$key\"";
        }
        if ($isMinimized) {
            return '';
        }

        return $key . '="' . ($escape ? e($value) : $value) . '"';
    }

    /**
     * Adds a class and returns a unique list either in array or space separated
     *
     * @param array|string $input The array or string to add the class to
     * @param array<string>|string $newClass the new class or classes to add
     * @param string $useIndex if you are inputting an array with an element other than default of 'class'.
     *
     * @return array<string>|string
     */
    /*
    public function addClass($input, $newClass, string $useIndex = 'class')
    {
        // NOOP
        if (empty($newClass)) {
            return $input;
        }

        if (is_array($input)) {
            $class = Hash::get($input, $useIndex, []);
        } else {
            $class = $input;
            $input = [];
        }

        // Convert and sanitise the inputs
        if (!is_array($class)) {
            if (is_string($class) && !empty($class)) {
                $class = explode(' ', $class);
            } else {
                $class = [];
            }
        }

        if (is_string($newClass)) {
            $newClass = explode(' ', $newClass);
        }

        $class = array_unique(array_merge($class, $newClass));

        $input = Hash::insert($input, $useIndex, $class);

        return $input;
    }*/
}
