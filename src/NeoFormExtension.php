<?php

namespace Efabrica\NeoForms;

use Latte\CompileException;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\Compiler;
use Latte\PhpWriter;

class NeoFormExtension extends MacroSet
{
    public static function install(Compiler $compiler): void
    {
        $me = new static($compiler);
        $me->addMacro('neoForm', fn($n, $w) => $me->neoFormStart($n, $w), fn($n, $w) => $me->neoFormEnd($n, $w));
        $me->addMacro('formRow', fn($n, $w) => $me->neoFormRow($n, $w));
        $me->addMacro('formRest', fn($n, $w) => $me->neoFormRest($n, $w));
        $me->addMacro('formSection', fn($n, $w) => $me->neoSectionStart($n, $w), fn($n, $w) => $me->neoSectionEnd($n, $w));
    }

    public function neoFormStart(MacroNode $node, PhpWriter $writer)
    {
        $this->validate($node);

        $name = $node->tokenizer->fetchWord();
        if ($name === null || $name === false) {
            throw new CompileException('Missing form name in ' . $node->getNotation());
        }

        $node->replaced = true;
        $node->tokenizer->reset();
        return $writer->write(
            'echo $this->global->neoFormRenderer->formStart($form = $this->global->formsStack[] = '
            . ($name[0] === '$'
                ? 'is_object($ʟ_tmp = %node.word) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]'
                : '$this->global->uiControl[%node.word]')
            . ', %node.array)'
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
        return $writer->write('echo $this->global->neoFormRenderer->row(%node.word, %node.array);'. " /* line $node->startLine */;");
    }

    public function neoFormRest(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->formRest('.$node->args.');'. " /* line $node->startLine */;");
    }

    public function neoSectionStart(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->sectionStart(%node.args);'. " /* line $node->startLine */;");
    }

    public function neoSectionEnd(MacroNode $node, PhpWriter $writer): string
    {
        $this->validate($node);
        return $writer->write('echo $this->global->neoFormRenderer->sectionEnd($translator->translate(%node.args));'. " /* line $node->startLine */;");
    }

    private function validate(MacroNode $node)
    {
        if ($node->modifiers) {
            throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
        }

        if ($node->prefix) {
            throw new CompileException('neoForm does not support n:attribute');
        }

        if (empty($node->args)) {
            throw new CompileException('Missing arguments in ' . $node->getNotation());
        }
    }
}
