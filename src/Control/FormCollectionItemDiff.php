<?php

namespace Efabrica\NeoForms\Control;

class FormCollectionItemDiff
{
    private array $oldRow;
    private array $newRow;
    private array $diff = [];

    public function __construct(array $oldRow, array $newRow)
    {
        $this->oldRow = $oldRow;
        $this->newRow = $newRow;
        foreach ($this->oldRow as $key => $oldValue) {
            $newValue = $this->newRow[$key] ?? null;
            if ($newValue !== $oldValue) {
                $this->diff[$key] = $newValue;
            }
        }
        foreach ($this->newRow as $key => $newValue) {
            $oldValue = $this->oldRow[$key] ?? null;
            if ($oldValue !== $newValue) {
                $this->diff[$key] = $newValue;
            }
        }
    }

    public function getOldRow(): array
    {
        return $this->oldRow;
    }

    public function getNewRow(): array
    {
        return $this->newRow;
    }

    /**
     * @return array<string, mixed> You can use this diff to update the row in the database.
     */
    public function getDiff(): array
    {
        return $this->diff;
    }
}
