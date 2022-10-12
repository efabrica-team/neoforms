<?php

namespace Efabrica\NeoForms\Control;

use Nette\Utils\Json;

class StaticTags extends Tags
{
    /**
     * @param string|null                     $label
     * @param string[]|array{value: string}[] $choices Either string[] or array of ['value' => string]
     * @param bool                            $allowCustomTags
     * @param string|null                     $placeholder
     */
    public function __construct(
        ?string $label,
        array $choices,
        bool $allowCustomTags = false,
        ?string $placeholder = null
    ) {
        parent::__construct(
            $label,
            ['type' => $allowCustomTags ? 'variable' : 'const', 'choices' => self::formatChoices($choices)],
            $placeholder
        );
    }

    public static function formatChoices(array $choices): array
    {
        if (is_string($choices[0] ?? null)) {
            return array_map(static fn($c) => ['value' => $c], $choices);
        }
        return $choices;
    }

    public function setSelectedChoices(array $choices): self
    {
        $choices = self::formatChoices($choices);
        $this->value = $choices;
        $this->rawValue = Json::encode($choices);
        return $this;
    }
}
