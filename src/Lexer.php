<?php

namespace Naotake51\Evaluation;

use Naotake51\Evaluation\Nodes\Node;
use Naotake51\Evaluation\Nodes\AdditiveNode;
use Naotake51\Evaluation\Nodes\MultiplicativeNode;
use Naotake51\Evaluation\Nodes\NumberNode;

/**
 * expr    = mul ("+" mul | "-" mul)*
 * mul     = primary ("*" primary | "/" primary)*
 * primary = num | "(" expr ")"
 *
 */
class Lexer {
    public function __invoke(array $tokens): ?Node {
        if (count($tokens) === 0) {
            return null;
        }

        [$root, $end] = $this->expr($tokens, 0);
        if (count($tokens) !== $end) {
            throw new \Exception('Syntax Error');
        }
        return $root;
    }

    private function expr(array $tokens, int $p): array {
        [$left, $p] = $this->mul($tokens, $p);
        while ($this->equal($tokens, $p, 'OPERATOR', ['+', '-'])) {
            $operator = $tokens[$p]->expression;
            $p++;
            [$right, $p] = $this->mul($tokens, $p);
            $left = new AdditiveNode($left, $right, $operator);
        }
        return [$left, $p];
    }

    private function mul(array $tokens, int $p): array {
        [$left, $p] = $this->primary($tokens, $p);
        while ($this->equal($tokens, $p, 'OPERATOR', ['*', '/', '%'])) {
            $operator = $tokens[$p]->expression;
            $p++;
            [$right, $p] = $this->primary($tokens, $p);
            $left = new MultiplicativeNode($left, $right, $operator);
        }
        return [$left, $p];
    }

    private function primary(array $tokens, int $p): array {
        if ($this->equal($tokens, $p, 'L_PAREN')) {
            $p++;

            [$expr, $p] = $this->expr($tokens, $p);

            $this->need($tokens, $p, 'R_PAREN');
            $p++;

            return [$expr, $p];
        } else {
            $this->need($tokens, $p, 'NUMBER');
            return [new NumberNode($tokens[$p]->expression), $p + 1];
        }
    }

    private function equal(array $tokens, int $p, string $type, $expression = null): bool {
        if (!($p < count($tokens))) {
            return false;
        }

        $token = $tokens[$p];
        return $token->type === $type &&
            (
                $expression === null ||
                (is_string($expression) && $token->expression === $expression) ||
                (is_array($expression) && in_array($token->expression, $expression, true))
            );
    }

    private function need(array $tokens, int $p, string $type, $expression = null) {
        if (!$this->equal($tokens, $p, $type, $expression)) {
            throw new \Exception('Syntax Error');
        }
    }
}