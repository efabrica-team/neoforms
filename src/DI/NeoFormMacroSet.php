<?php

namespace Efabrica\NeoForms\DI;

use Efabrica\NeoForms\Build\NeoFormControl;
use Latte\CompileException;
use Latte\Compiler;
use Latte\Engine;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use Nette\Utils\ArrayHash;
use Nette\Utils\Html;

if (Engine::VERSION_ID >= 30000) {
    class_alias(ArrayHash::class, MacroSet::class);
}

class NeoFormMacroSet extends MacroSet
{
    public static function install(Compiler $compiler): void
    {
        $me = new self($compiler);
        $me->addMacro('neoForm', fn($n, $w) => $me->neoFormStart($n, $w), fn($n, $w) => $me->neoFormEnd($n, $w));
        $me->addMacro('formRow', fn($n, $w) => $me->neoFormRow($n, $w));
        $me->addMacro('formGroup', fn($n, $w) => $me->neoFormGroup($n, $w));
        $me->addMacro('formRest', fn($n, $w) => $me->neoFormRest($n, $w));
        $me->addMacro('formErrors', fn($n, $w) => $me->neoFormErrors($n, $w));
        $me->addMacro('formInput', fn($n, $w) => $me->neoFormInput($n, $w));
        $me->addMacro('formLabel', fn($n, $w) => $me->neoFormLabel($n, $w));
    }

    public function neoFormStart(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);

        $name = $node->tokenizer->fetchWord();
        if ($name === null) {
            throw new CompileException('Missing form name in ' . $node->getNotation());
        }

        $node->replaced = true;
        $node->tokenizer->reset();
        return $writer->write(
            '$form = '
            . ($name[0] === '$'
                ? 'is_object($ʟ_tmp = %node.word) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]'
                : '$this->global->uiControl[%node.word]') . ';'
            . 'if ($form instanceof ' . NeoFormControl::class . ') $form = $form->form;'
            . '$ʟ_formGen = $this->global->neoFormRenderer->form($this->global->formsStack[] = $form, %node.array);'
            . '$ʟ_formGen->current();ob_start();'
            . " /* line $node->startLine */;"
        );
    }

    public function neoFormEnd(MacroNode $node, PhpWriter $writer): string
    {
        return $writer->write('$ʟ_formGen->send(' . Html::class . '::fromHtml(ob_get_clean()));'
            . 'echo $ʟ_formGen->getReturn();' . "/* line $node->startLine */;");
    }

    public function neoFormRow(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->formRow(%node.word, %node.array);' . " /* line $node->startLine */;");
    }

    public function neoFormGroup(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->formGroup($form, %node.word);' . " /* line $node->startLine */;");
    }

    public function neoFormRest(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->formRest(%node.word, %node.array);' . " /* line $node->startLine */;");
    }

    public function neoFormErrors(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->formErrors(%node.word, %node.array);' . " /* line $node->startLine */;");
    }

    public function neoFormInput(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->formInput(%node.word, %node.array);' . " /* line $node->startLine */;");
    }

    public function neoFormLabel(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->formLabel(%node.word, %node.array);' . " /* line $node->startLine */;");
    }

    private function validate(MacroNode $node): void
    {
        if (trim($node->modifiers) !== '') {
            throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
        }

        if (trim((string)$node->prefix) !== '') {
            throw new CompileException('neoForm does not support n:attribute');
        }

        if (empty($node->args)) {
            throw new CompileException('Missing arguments in ' . $node->getNotation());
        }
    }
}
