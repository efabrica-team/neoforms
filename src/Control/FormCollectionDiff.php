<?php

namespace Efabrica\NeoForms\Control;

use Generator;

class FormCollectionDiff
{
    private array $previousData;
    private array $newData;

    public function __construct(array $httpData)
    {
        $this->previousData = json_decode($httpData[FormCollection::ORIGINAL_DATA], true, 512, JSON_THROW_ON_ERROR);
        unset($httpData[FormCollection::ORIGINAL_DATA]);
        $this->newData = $httpData;
    }

    public function isModified(): bool
    {
        return $this->getAdded()->valid() || $this->getDeleted()->valid() || $this->getModified()->valid();
    }

    public function getAdded(): Generator
    {
        foreach ($this->newData as $value) {
            if (!isset($value[FormCollectionItem::UNIQID])) {
                yield self::cleanArray($value);
            }
        }
    }

    public function getDeleted(): Generator
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
                yield self::cleanArray($value);
            }
        }
    }

    /**
     * @return FormCollectionItemDiff[]&Generator
     */
    public function getModified(): Generator
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

    public static function cleanArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::cleanArray($value);
            }
            if ($key === FormCollection::ORIGINAL_DATA || $key === FormCollectionItem::UNIQID) {
                unset($array[$key]);
            }
        }
        return $array;
    }
}
