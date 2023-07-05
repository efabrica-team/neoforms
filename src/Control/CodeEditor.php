<?php

namespace Efabrica\NeoForms\Control;

use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;
use Nette\Utils\Json;

/**
 * Ace Code Editor
 */
class CodeEditor extends TextArea
{
    public const MODE_JAVASCRIPT = 'ace/mode/javascript';
    public const MODE_HTML = 'ace/mode/html';
    public const MODE_CSS = 'ace/mode/css';
    public const MODE_PHP = 'ace/mode/php';
    public const MODE_JSON = 'ace/mode/json';
    public const MODE_LATTE = 'ace/mode/latte';
    public const MODE_PLAIN_TEXT = 'ace/mode/plain_text';
    public const MODE_TWIG = 'ace/mode/twig';
    public const MODES = [self::MODE_JAVASCRIPT, self::MODE_HTML, self::MODE_CSS, self::MODE_PHP, self::MODE_JSON, self::MODE_LATTE, self::MODE_PLAIN_TEXT, self::MODE_TWIG];

    private string $mode;

    /**
     * @param self::MODE_* $mode
     * @param string|object $label
     */
    public function __construct(string $mode, $label = null)
    {
        parent::__construct($label);
        $this->mode = $mode;
    }

    public function getControl(): Html
    {
        $control = parent::getControl();
        $control->setAttribute('class', 'js-code-editor');
        $control->setAttribute('data-config', Json::encode(['mode' => $this->mode]));
        return $control;
    }
}
