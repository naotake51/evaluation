<?php

namespace Naotake51\Evaluation;

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
        ['/^,/', 'COMMA'],
        ['/^[A-Za-z][A-Za-z_0-9]*/', 'IDENT'],
    ];

    public function __invoke(string $expression) {
        $p = 0;
        $tokens = [];
        while ($p < strlen($expression)) {
            $token = $this->matchToken(substr($expression, $p));
            if ($token === null) {
                return false; // TODO: より具体的なエラー内容を返したい
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