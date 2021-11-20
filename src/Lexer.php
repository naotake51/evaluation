<?php

namespace Naotake51\Evaluation;

use Naotake51\Evaluation\Token;
use Naotake51\Evaluation\Nodes\Node;
use Naotake51\Evaluation\Nodes\IntegerNode;
use Naotake51\Evaluation\Nodes\FloatNode;
use Naotake51\Evaluation\Nodes\StringNode;
use Naotake51\Evaluation\Nodes\BooleanNode;
use Naotake51\Evaluation\Nodes\ArrayNode;
use Naotake51\Evaluation\Nodes\FunctionNode;
use Naotake51\Evaluation\Errors\SyntaxError;

/**
 * 構文解析モジュール
 *
 * expr    = mul ("+" mul | "-" mul)*
 * mul     = primary ("*" primary | "/" primary)*
 * primary = integer | float | string | boolean | array | "(" expr ")" | func
 * array   = "[" (expr ("+" expr)*)? "]"
 * func    = ident "(" (expr ("+" expr)*)? ")"
 */
class Lexer {
    private $exprOperators = [
        '+' => '__add',
        '-' => '__sub',
    ];

    private $mulOperators = [
        '*' => '__mul',
        '/' => '__div',
        '%' => '__mod',
    ];

    /**
     * 構文解析
     *
     * @param  Token[] $tokens
     * @return Node
     * @throws SyntaxError
     */
    public function __invoke(array $tokens): Node {
        if (count($tokens) === 0) {
            throw new SyntaxError('empty tokens.');
        }

        [$root, $end] = $this->expr($tokens, 0);
        if (count($tokens) !== $end) {
            throw new SyntaxError('syntax error.');
        }
        return $root;
    }

    private function expr(array $tokens, int $p): array {
        [$left, $p] = $this->mul($tokens, $p);
        while ($this->equal($tokens, $p, 'OPERATOR', array_keys($this->exprOperators))) {
            $magicFunction = $this->exprOperators[$tokens[$p]->expression];
            $p++;
            [$right, $p] = $this->mul($tokens, $p);
            $left = new FunctionNode($magicFunction, [$left, $right]);
        }
        return [$left, $p];
    }

    private function mul(array $tokens, int $p): array {
        [$left, $p] = $this->primary($tokens, $p);
        while ($this->equal($tokens, $p, 'OPERATOR', array_keys($this->mulOperators))) {
            $magicFunction = $this->mulOperators[$tokens[$p]->expression];
            $p++;
            [$right, $p] = $this->primary($tokens, $p);
            $left = new FunctionNode($magicFunction, [$left, $right]);
        }
        return [$left, $p];
    }

    private function primary(array $tokens, int $p): array {
        if ($this->equal($tokens, $p, 'INTEGER')) {
            return [new IntegerNode($tokens[$p]->expression), $p + 1];
        } else if ($this->equal($tokens, $p, 'FLOAT')) {
            return [new FloatNode($tokens[$p]->expression), $p + 1];
        } else if ($this->equal($tokens, $p, 'STRING')) {
            return [new StringNode($tokens[$p]->expression), $p + 1];
        } else if ($this->equal($tokens, $p, 'BOOLEAN')) {
            return [new BooleanNode($tokens[$p]->expression), $p + 1];
        } else if ($this->equal($tokens, $p, 'L_BRACKET')) {
            return $this->array($tokens, $p);
        } else if ($this->equal($tokens, $p, 'L_PAREN')) {
            $p++;
            [$expr, $p] = $this->expr($tokens, $p);
            $this->need($tokens, $p, 'R_PAREN');
            $p++;
            return [$expr, $p];
        } else {
            return $this->func($tokens, $p);
        }
    }

    private function array(array $tokens, int $p): array {
        $this->need($tokens, $p, 'L_BRACKET');
        $p++;

        $items = [];
        if (!$this->equal($tokens, $p, 'R_BRACKET')) {
            do {
                [$item, $p] = $this->expr($tokens, $p);
                $items[] = $item;
            } while ($this->equal($tokens, $p, 'COMMA') && $p++);
            $this->need($tokens, $p, 'R_BRACKET');
        }
        $p++;
        return [new ArrayNode($items), $p];
    }

    private function func(array $tokens, int $p): array {
        $this->need($tokens, $p, 'IDENT');
        $identify = $tokens[$p]->expression;
        $p++;

        $this->need($tokens, $p, 'L_PAREN');
        $p++;

        $arguments = [];
        if (!$this->equal($tokens, $p, 'R_PAREN')) {
            do {
                [$argument, $p] = $this->expr($tokens, $p);
                $arguments[] = $argument;
            } while ($this->equal($tokens, $p, 'COMMA') && $p++);
            $this->need($tokens, $p, 'R_PAREN');
        }
        $p++;
        return [new FunctionNode($identify, $arguments), $p];
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
            throw new SyntaxError('syntax error.');
        }
    }
}