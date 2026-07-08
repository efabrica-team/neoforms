<?php

namespace Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Build\NeoContainer;
use Nette\Forms\Controls\HiddenField;
use Nette\Utils\ArrayHash;

class FormCollectionItem extends NeoContainer
{
    public const UNIQID = '__neoFC_uniqId__';

    private ?HiddenField $uniqId = null;

    public function __construct(bool $prototype = false)
    {
        $this->setSingleRender(true);
        if (!$prototype) {
            $this->uniqId = $this->addHidden(self::UNIQID, uniqid());
        }
    }

    /**
     * @return array<string, mixed>|object
     */
    public function getUntrustedValues(string|object|null $returnType = ArrayHash::class, ?array $controls = null): object|array
    {
        $uniqId = $this->uniqId;
        $removed = false;
        if ($uniqId !== null && $uniqId->getParent() !== null && $uniqId->getValue() === '') {
            $this->removeComponent($uniqId);
            $removed = true;
        }
        try {
            return parent::getUntrustedValues($returnType, $controls);
        } finally {
            if ($removed && $uniqId !== null) {
                $this->addComponent($uniqId, self::UNIQID);
            }
        }
    }
}
