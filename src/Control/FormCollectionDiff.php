<?php

namespace Efabrica\NeoForms\Control;

class FormCollectionDiff
{
    private array $previousData;
    private array $newData;

    public function __construct(array $previousData, array $newData)
    {
        $this->previousData = $previousData;
        $this->newData = $newData;
    }

    public function getAdded(): iterable
    {
        foreach ($this->newData as $value) {
            if (!isset($value[FormCollectionItem::UNIQID])) {
                yield $value;
            }
        }
    }

    public function getDeleted(): iterable
    {
        foreach ($this->previousData as $value) {
            if (!isset($value[FormCollectionItem::UNIQID])) {
                continue;
            }
            $previousUniqid = $value[FormCollectionItem::UNIQID];
            $found = false;
            foreach ($this->newData as $newValue) {
                if (isset($newValue[FormCollectionItem::UNIQID]) && $newValue[FormCollectionItem::UNIQID] === $previousUniqid) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                yield $value;
            }
        }
    }

    /**
     * @return FormCollectionItemDiff[]
     */
    public function getModified(): iterable
    {
        foreach ($this->previousData as $previousValue) {
            if (!isset($previousValue[FormCollectionItem::UNIQID])) {
                continue;
            }
            $previousUniqid = $previousValue[FormCollectionItem::UNIQID];
            foreach ($this->newData as $newValue) {
                if (!isset($newValue[FormCollectionItem::UNIQID]) || $newValue[FormCollectionItem::UNIQID] !== $previousUniqid) {
                    continue;
                }
                $diff = new FormCollectionItemDiff($previousValue, $newValue);
                if ($diff->getDiff() !== []) {
                    yield $diff;
                }
            }
        }
    }

    public function areArraysRecursivelyEqual(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }
        foreach ($a as $key => $value) {
            if (!isset($b[$key])) {
                return false;
            }
            if (is_array($value)) {
                if (!is_array($b[$key])) {
                    return false;
                }
                if (!$this->areArraysRecursivelyEqual($value, $b[$key])) {
                    return false;
                }
            } elseif ($value !== $b[$key]) {
                return false;
            }
        }
        return true;
    }
}
