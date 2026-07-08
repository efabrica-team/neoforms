<?php

namespace Tests\Efabrica\NeoForms\DI\Node;

use Efabrica\NeoForms\DI\Node\LatteNodeCompat;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use PHPUnit\Framework\TestCase;

class LatteNodeCompatTest extends TestCase
{
    public function testEndPositionReturnsLastTagRangeWhenPropertyExists(): void
    {
        $node = new class extends StatementNode {
            public function print(PrintContext $context): string
            {
                return '';
            }

            public function &getIterator(): \Generator
            {
                if (false) {
                    yield;
                }
            }
        };
        $node->tagRanges = ['first', 'last'];

        $this->assertSame('last', LatteNodeCompat::endPosition($node));
    }

    public function testEndPositionReturnsFalseForEmptyTagRanges(): void
    {
        $node = new class extends StatementNode {
            public function print(PrintContext $context): string
            {
                return '';
            }

            public function &getIterator(): \Generator
            {
                if (false) {
                    yield;
                }
            }
        };
        $node->tagRanges = [];

        $this->assertFalse(LatteNodeCompat::endPosition($node));
    }
}
