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
use Naotake51\Evaluation\Nodes\ObjectNode;
use Naotake51\Evaluation\Errors\SyntaxError;

/**
 * 構文解析モジュール
 *
 * expr    = or
 * or      = and ("||" and)*
 * and     = compare ("&&" compare)*
 * compare = add ("===" add | "!==" add| "==" add| "!=" add)*
 * add     = mul ("+" mul | "-" mul)*
 * mul     = not ("*" not | "/" not)*
 * not     = ("!" not | val)*
 * val     = integer | float | string | boolean | array | primary | func
 * primary = "(" expr ")"
 * array   = "[" (expr ("," expr)*)? "]"
 * object  = "{" (string ":" expr ("," string ":" expr)*)? "}"
 * func    = ident "(" (expr ("+" expr)*)? ")"
 */
class Parser
{
    private $orOperators = [
        '||' => '__or',
    ];

    private $andOperators = [
        '&&' => '__and',
    ];

    private $compareOperators = [
        '===' => '__equal_strict',
        '!==' => '__not_equal_strict',
        '==' => '__equal',
        '!=' => '__not_equal',
    ];

    private $addOperators = [
        '+' => '__add',
        '-' => '__sub',
    ];

    private $mulOperators = [
        '*' => '__mul',
        '/' => '__div',
        '%' => '__mod',
    ];

    private $notOperators = [
        '!' => '__not',
    ];

    /**
     * 構文解析
     *
     * @param  Token[] $tokens
     * @return Node
     * @throws SyntaxError
     */
    public function __invoke(array $tokens): Node
    {
        if (count($tokens) === 0) {
            throw new SyntaxError('empty tokens.');
        }

        [$root, $end] = $this->expr($tokens, 0);
        if (count($tokens) !== $end) {
            throw new SyntaxError('syntax error.');
        }
        return $root;
    }

    private function expr(array $tokens, int $p): array
    {
        return $this->or($tokens, $p);
    }

    private function or(array $tokens, int $p): array
    {
        [$left, $p] = $this->and($tokens, $p);
        while ($this->equal($tokens, $p, 'OPERATOR', array_keys($this->orOperators))) {
            $magicFunction = $this->orOperators[$tokens[$p]->expression];
            $p++;
            [$right, $p] = $this->and($tokens, $p);
            $left = new FunctionNode($magicFunction, [$left, $right]);
        }
        return [$left, $p];
    }

    private function and(array $tokens, int $p): array
    {
        [$left, $p] = $this->compare($tokens, $p);
        while ($this->equal($tokens, $p, 'OPERATOR', array_keys($this->andOperators))) {
            $magicFunction = $this->andOperators[$tokens[$p]->expression];
            $p++;
            [$right, $p] = $this->compare($tokens, $p);
            $left = new FunctionNode($magicFunction, [$left, $right]);
        }
        return [$left, $p];
    }

    private function compare(array $tokens, int $p): array
    {
        [$left, $p] = $this->add($tokens, $p);
        while ($this->equal($tokens, $p, 'OPERATOR', array_keys($this->compareOperators))) {
            $magicFunction = $this->compareOperators[$tokens[$p]->expression];
            $p++;
            [$right, $p] = $this->add($tokens, $p);
            $left = new FunctionNode($magicFunction, [$left, $right]);
        }
        return [$left, $p];
    }

    private function add(array $tokens, int $p): array
    {
        [$left, $p] = $this->mul($tokens, $p);
        while ($this->equal($tokens, $p, 'OPERATOR', array_keys($this->addOperators))) {
            $magicFunction = $this->addOperators[$tokens[$p]->expression];
            $p++;
            [$right, $p] = $this->mul($tokens, $p);
            $left = new FunctionNode($magicFunction, [$left, $right]);
        }
        return [$left, $p];
    }

    private function mul(array $tokens, int $p): array
    {
        [$left, $p] = $this->not($tokens, $p);
        while ($this->equal($tokens, $p, 'OPERATOR', array_keys($this->mulOperators))) {
            $magicFunction = $this->mulOperators[$tokens[$p]->expression];
            $p++;
            [$right, $p] = $this->not($tokens, $p);
            $left = new FunctionNode($magicFunction, [$left, $right]);
        }
        return [$left, $p];
    }

    private function not(array $tokens, int $p): array
    {
        if ($this->equal($tokens, $p, 'OPERATOR', array_keys($this->notOperators))) {
            $magicFunction = $this->notOperators[$tokens[$p]->expression];
            $p++;
            [$val, $p] = $this->not($tokens, $p);
            return [new FunctionNode($magicFunction, [$val]), $p];
        } else {
            return $this->val($tokens, $p);
        }
    }

    private function val(array $tokens, int $p): array
    {
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
        } else if ($this->equal($tokens, $p, 'L_BRACE')) {
            return $this->object($tokens, $p);
        } else if ($this->equal($tokens, $p, 'L_PAREN')) {
            return $this->primary($tokens, $p);
        } else {
            return $this->func($tokens, $p);
        }
    }

    private function object(array $tokens, int $p): array
    {
        $object = [];

        $this->need($tokens, $p, 'L_BRACE');
        $p++;

        if (!$this->equal($tokens, $p, 'R_BRACE')) {
            do {
                $this->need($tokens, $p, 'STRING');
                $key = new StringNode($tokens[$p]->expression);
                $p++;
                $this->need($tokens, $p, 'COLON');
                $p++;
                [$value, $p] = $this->expr($tokens, $p);
                $object[] = ['key' => $key, 'value' => $value];
            } while ($this->equal($tokens, $p, 'COMMA') && $p++);
        }
        $this->need($tokens, $p, 'R_BRACE');
        $p++;

        return [new ObjectNode($object), $p];
    }

    private function primary(array $tokens, int $p): array
    {
        $this->need($tokens, $p, 'L_PAREN');
        $p++;
        [$expr, $p] = $this->expr($tokens, $p);
        $this->need($tokens, $p, 'R_PAREN');
        $p++;
        return [$expr, $p];
    }

    private function array(array $tokens, int $p): array
    {
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

    private function func(array $tokens, int $p): array
    {
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

    private function equal(array $tokens, int $p, string $type, $expression = null): bool
    {
        if (!($p < count($tokens))) {
            return false;
        }

        $token = $tokens[$p];
        return $token->type === $type &&
            ($expression === null ||
                (is_string($expression) && $token->expression === $expression) ||
                (is_array($expression) && in_array($token->expression, $expression, true))
            );
    }

    private function need(array $tokens, int $p, string $type, $expression = null)
    {
        if (!$this->equal($tokens, $p, $type, $expression)) {
            throw new SyntaxError('syntax error.');
        }
    }
}
