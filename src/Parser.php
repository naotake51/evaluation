<?php

namespace Naotake51\Evaluation;

use Naotake51\Evaluation\Errors\SyntaxError;

/**
 * 字句解析モジュール
 */
class Parser {
    private $parsedTokens = [
        ['/^\s+/', 'WHITE_SPACE'],
        ['/^[0-9]*\.[0-9]+/', 'FLOAT'],
        ['/^[0-9]+/', 'INTEGER'],
        ['/^true/', 'BOOLEAN'],
        ['/^false/', 'BOOLEAN'],
        ['/^True/', 'BOOLEAN'],
        ['/^False/', 'BOOLEAN'],
        ['/^TRUE/', 'BOOLEAN'],
        ['/^FALSE/', 'BOOLEAN'],
        ["/^'.*?(?<!\\\\)'/", 'STRING'], // 肯定の後読み
        ['/^".*?(?<!\\\\)"/', 'STRING'], // 肯定の後読み
        ['/^\+/', 'OPERATOR'],
        ['/^\-/', 'OPERATOR'],
        ['/^\*/', 'OPERATOR'],
        ['/^\//', 'OPERATOR'],
        ['/^%/', 'OPERATOR'],
        ['/^\./', 'OPERATOR'], // 文字列連結 NOTE:小数点数時より後である必要がある
        ['/^\(/', 'L_PAREN'],
        ['/^\)/', 'R_PAREN'],
        ['/^\[/', 'L_BRACKET'],
        ['/^\]/', 'R_BRACKET'],
        ['/^,/', 'COMMA'],
        ['/^[A-Za-z][A-Za-z_0-9]*/', 'IDENT'],
    ];

    /**
     * 字句解析
     *
     * @param  string $expression
     * @return Token[]
     * @throws SyntaxError
     */
    public function __invoke(string $expression): array {
        $p = 0;
        $tokens = [];
        while ($p < strlen($expression)) {
            $token = $this->matchToken(substr($expression, $p));
            if ($token === null) {
                throw new SyntaxError('syntax error.');
            }
            if ($token->type !== 'WHITE_SPACE') {
                $tokens[] = $token;
            }
            $p += strlen($token->expression);
        }
        return $tokens;
    }

    private function matchToken($expression) {
        foreach ($this->parsedTokens as [$pattern, $type]) {
            if (preg_match($pattern, $expression, $match)) {
                return new Token($type, $match[0]);
            }
        }
        return null;
    }
}