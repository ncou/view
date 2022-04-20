<?php

declare(strict_types=1);

namespace Chiron\View;

use Chiron\ResponseCreator\ResponseCreator;
use Chiron\View\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;

use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Core\Exception\CakeException;
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
final class StringTemplate
{
    /**
     * Runtime config
     *
     * @var array<string, mixed>
     */
    protected $_config = []; // TODO : vérifier l'utilité de cette variable !!!!

    /**
     * List of attributes that can be made compact.
     *
     * @var array<string, bool>
     */
    protected $_compactAttributes = [
        'allowfullscreen' => true,
        'async' => true,
        'autofocus' => true,
        'autoplay' => true,
        'checked' => true,
        'compact' => true,
        'controls' => true,
        'declare' => true,
        'default' => true,
        'defaultchecked' => true,
        'defaultmuted' => true,
        'defaultselected' => true,
        'defer' => true,
        'disabled' => true,
        'enabled' => true,
        'formnovalidate' => true,
        'hidden' => true,
        'indeterminate' => true,
        'inert' => true,
        'ismap' => true,
        'itemscope' => true,
        'loop' => true,
        'multiple' => true,
        'muted' => true,
        'nohref' => true,
        'noresize' => true,
        'noshade' => true,
        'novalidate' => true,
        'nowrap' => true,
        'open' => true,
        'pauseonexit' => true,
        'readonly' => true,
        'required' => true,
        'reversed' => true,
        'scoped' => true,
        'seamless' => true,
        'selected' => true,
        'sortable' => true,
        'truespeed' => true,
        'typemustmatch' => true,
        'visible' => true,
    ];

    /**
     * Contains the list of compiled templates
     *
     * @var array<string, array>
     */
    protected $_compiled = [];

    /**
     * Constructor.
     *
     * @param array<string, mixed> $config A set of templates to add.
     */
    // TODO : renommer le paramétre en $templates !!!!
    public function __construct(array $config = [])
    {
        $this->add($config);
    }

    /**
     * Registers a list of templates by name
     *
     * ### Example:
     *
     * ```
     * $templater->add([
     *   'link' => '<a href="{{url}}">{{title}}</a>'
     *   'button' => '<button>{{text}}</button>'
     * ]);
     * ```
     *
     * @param array<string> $templates An associative list of named templates.
     * @return $this
     */
    // TODO : passer la méthode en private et le return type en void
    // TODO : eventuellement virer cette méthode et passer les 2 lignes de code dans le constructeur
    public function add(array $templates)
    {
        //$this->setConfig($templates);
        $this->_config = $templates;
        $this->_compileTemplates(array_keys($templates));

        return $this;
    }

    /**
     * Compile templates into a more efficient printf() compatible format.
     *
     * @param array<string> $templates The template names to compile. If empty all templates will be compiled.
     * @return void
     */
    protected function _compileTemplates(array $templates = []): void
    {
        // TODO : virer l'initialisation du paramétre $templates par défaut à [], ce qui permettra de virer le if(empty) ci dessous !!!
        if (empty($templates)) {
            $templates = array_keys($this->_config);
        }
        foreach ($templates as $name) {
            //$template = $this->getConfig($name);
            $template = $this->_config[$name] ?? null; // TODO : je pense pas que ce cas peut arriver !!!!
            if ($template === null) {
                $this->_compiled[$name] = [null, null];
            }

            $template = str_replace('%', '%%', $template);
            preg_match_all('#\{\{([\w\._]+)\}\}#', $template, $matches);
            $this->_compiled[$name] = [
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
     * @return string Formatted string
     * @throws \RuntimeException If template not found.
     */
    // TODO : renommer en formatTemplate()
    public function format(string $name, array $data): string
    {
        if (!isset($this->_compiled[$name])) {
            throw new RuntimeException("Cannot find template named '$name'.");
        }

        [$template, $placeholders] = $this->_compiled[$name];

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
     * @return string Composed attributes.
     */
    public function formatAttributes(?array $options, ?array $exclude = null): string
    {
        $insertBefore = ' ';
        $options = (array)$options + ['escape' => true];

        if (!is_array($exclude)) {
            $exclude = [];
        }

        $exclude = ['escape' => true, 'idPrefix' => true, 'templateVars' => true, 'fieldName' => true]
            + array_flip($exclude);
        $escape = $options['escape'];
        $attributes = [];

        foreach ($options as $key => $value) {
            if (!isset($exclude[$key]) && $value !== false && $value !== null) {
                $attributes[] = $this->_formatAttribute((string)$key, $value, $escape);
            }
        }
        $out = trim(implode(' ', $attributes));

        return $out ? $insertBefore . $out : '';
    }

    /**
     * Formats an individual attribute, and returns the string value of the composed attribute.
     * Works with minimized attributes that have the same value as their name such as 'disabled' and 'checked'
     *
     * @param string $key The name of the attribute to create
     * @param array<string>|string $value The value of the attribute to create.
     * @param bool $escape Define if the value must be escaped
     * @return string The composed attribute.
     */
    protected function _formatAttribute(string $key, $value, $escape = true): string
    {
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (is_numeric($key)) {
            return "$value=\"$value\"";
        }
        $truthy = [1, '1', true, 'true', $key];
        $isMinimized = isset($this->_compactAttributes[$key]);
        if (!preg_match('/\A(\w|[.-])+\z/', $key)) {
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
