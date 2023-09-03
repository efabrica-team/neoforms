<?php

namespace Efabrica\NeoForms\DI;

use Efabrica\NeoForms\Build\NeoFormControl;
use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class NeoFormMacroSet extends MacroSet
{
    public static function install(Compiler $compiler): void
    {
        $me = new self($compiler);
        $me->addMacro('neoForm', fn($n, $w) => $me->neoFormStart($n, $w), fn($n, $w) => $me->neoFormEnd($n, $w));
        $me->addMacro('formRow', fn($n, $w) => $me->neoFormRow($n, $w));
        $me->addMacro('formGroup', fn($n, $w) => $me->neoFormGroup($n, $w));
        $me->addMacro('formRest', fn($n, $w) => $me->neoFormRest($n, $w));
        $me->addMacro('formSection', fn($n, $w) => $me->neoSectionStart($n, $w), fn($n, $w) => $me->neoSectionEnd($n, $w));
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
            . 'echo $this->global->neoFormRenderer->formStart($this->global->formsStack[] = $form, %node.array);'
            . " /* line $node->startLine */;"
        );
    }

    public function neoFormEnd(MacroNode $node, PhpWriter $writer): string
    {
        return $writer->write('echo $this->global->neoFormRenderer->formEnd(array_pop($this->global->formsStack), %node.array);');
    }

    public function neoFormRow(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->formRow(%node.word, %node.array);' . " /* line $node->startLine */;");
    }

    public function neoFormGroup(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->formGroup(%node.word);' . " /* line $node->startLine */;");
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

    public function neoSectionStart(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->sectionStart(%node.word, %node.array);' . " /* line $node->startLine */;");
    }

    public function neoSectionEnd(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->sectionEnd($translator->translate(%node.word, %node.array));' . " /* line $node->startLine */;");
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
