<?php

namespace Naotake51\Evaluation;

use Closure;

/**
 * パラメーターに対応するファンクションを解決する
 */
class ArgumentsResolver {
    /**
     * パラメーターに対応するファンクションを返す
     *
     * @param  string $identify
     * @param  array  $arguments
     * @param  array  $mappedArgsFunctions
     * @return Closure|null
     */
    public function __invoke(array $arguments, array $mappedArgsFunctions): ?Closure {
        foreach ($mappedArgsFunctions as $defineArguments => $function) {
            if ($this->matchArguments($arguments, $defineArguments)) {
                return $function;
            }
        }
        return null;
    }

    /**
     * パラメーターリストが一致しているか
     *
     * @param  array  $arguments
     * @param  string $defineArguments
     * @return bool
     * @throws ArgumentError
     */
    private function matchArguments(array $arguments, string $defineArguments): bool {
        $defineArgs = explode(',', $defineArguments);
        if (count($defineArgs) !== count($arguments)) {
            return false;
        }

        foreach ($defineArgs as $i => $defineArg) {
            if (!$this->matchType($arguments[$i], $defineArg)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 型チェック
     *
     * @param  mixed  $value
     * @param  string $defineArg
     * @return bool
     */
    private function matchType($value, string $defineArg): bool {
        foreach (explode('|', $defineArg) as $type) {
            $type = trim($type);
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
            } elseif ($type === 'array' && is_array($value)) {
                return true;
            } elseif ($type === 'object' && is_object($value)) {
                return true;
            } elseif ($type === 'null' && is_null($value)) {
                return true;
            } elseif ($type === 'mixed') {
                return true;
            }
        }
        return false;
    }
}
