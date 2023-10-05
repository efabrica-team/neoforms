<?php

namespace Efabrica\NeoForms\Control;

class FormCollectionItemDiff
{
    private array $oldRow;

    private array $newRow;

    private array $diff = [];

    public function __construct(array $oldRow, array $newRow)
    {
        foreach ($oldRow as $key => $oldValue) {
            if ($key === FormCollection::ORIGINAL_DATA) {
                continue;
            }
            $newValue = $newRow[$key] ?? null;
            if (isset($newValue[FormCollection::ORIGINAL_DATA])) {
                continue;
            }
            if ($this->notEqual($newValue, $oldValue)) {
                $this->diff[$key] = $newValue;
            }
        }
        foreach ($newRow as $key => $newValue) {
            if ($key === FormCollection::ORIGINAL_DATA) {
                continue;
            }
            if (isset($newValue[FormCollection::ORIGINAL_DATA])) {
                $inDiff = new FormCollectionDiff($newValue);
                if ($inDiff->isNotEmpty()) {
                    $this->diff[$key] = $inDiff;
                }
                continue;
            }
            $oldValue = $oldRow[$key] ?? null;
            if ($this->notEqual($oldValue, $newValue)) {
                $this->diff[$key] = $newValue;
            }
        }
        $this->oldRow = FormCollectionDiff::cleanArray($oldRow);
        $this->newRow = FormCollectionDiff::cleanArray($newRow);
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

    /**
     * @param mixed $newValue
     * @param mixed $oldValue
     * @return bool
     */
    private function notEqual($newValue, $oldValue): bool
    {
        $newValue = is_scalar($newValue) ? (string)$newValue : $newValue;
        $oldValue = is_scalar($oldValue) ? (string)$oldValue : $oldValue;
        if (is_array($newValue) && is_array($oldValue)) {
            foreach ($newValue as $key => $value) {
                if ($key === FormCollection::ORIGINAL_DATA) {
                    continue;
                }
                if ($this->notEqual($value, $oldValue[$key] ?? null)) {
                    return true;
                }
            }
            return false;
        }
        return $newValue !== $oldValue;
    }
}
