<?php

namespace Naotake51\Evaluation;

use Naotake51\Evaluation\Errors\ArgumentError;

/**
 * パラメーターチェック用クラス
 */
class ArgumentValidator {
    /**
     * パラメーターチェック
     *
     * @param  string $identify
     * @param  array  $arguments
     * @param  array  $defines
     * @return void
     * @throws ArgumentError
     */
    public function __invoke(string $identify, array $arguments, array $defines) {
        if (count($defines) !== count($arguments)) {
            throw new ArgumentError("function $identify arguments not match " . count($defines) . '.');
        }

        foreach ($defines as $i => $define) {
            $types = explode('|', $define);
            if (!$this->matchType($arguments[$i], $types)) {
                $number = $i + 1;
                throw new ArgumentError("function $identify argument $number need $define type.");
            }
        }
    }

    /**
     * 型チェック
     *
     * @param  mixed    $value
     * @param  string[] $types
     * @return bool
     */
    private function matchType($value, array $types): bool {
        foreach ($types as $type) {
            if ($type === 'numeric' && is_numeric($value)) {
                return true;
            } elseif ($type === 'integer' && is_integer($value)) {
                return true;
            } elseif ($type === 'float' && is_float($value)) {
                return true;
            } elseif ($type === 'string' && is_string($value)) {
                return true;
            } elseif ($type === 'bool' && is_bool($value)) {
                return true;
            } elseif ($type === 'null' && is_null($value)) {
                return true;
            }
        }
        return false;
    }
}
