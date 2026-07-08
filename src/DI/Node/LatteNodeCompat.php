<?php

namespace Efabrica\NeoForms\DI\Node;

use Latte\Compiler\Nodes\StatementNode;

/**
 * Latte has changed the way a compiler node exposes its end position across
 * minor versions before (a bare `$endLine` property was replaced by the
 * `tagRanges` array - see CHANGELOG.md). Isolating that lookup here means the
 * next such change is a one-line fix in one file instead of a hunt through
 * every Node::print() implementation under release pressure.
 *
 * @internal
 */
final class LatteNodeCompat
{
    public static function endPosition(StatementNode $node): mixed
    {
        if (property_exists($node, 'tagRanges')) {
            return end($node->tagRanges);
        }
        return $node->endLine ?? $node->position ?? null;
    }
}
