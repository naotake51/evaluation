# Overview
naotake51/evaluation is a Composer package for creating a simple expression evaluation module.
It allows you to register your own functions to evaluate expressions (strings).

# Using

```php
use Naotake51\Evaluation\Evaluation;

$evaluation = new Evaluation([
    'square' => function (array $arguments) {
        return $arguments[0] * $arguments[0];
    }
]);
$result = $evaluation('square(2) + square(2)'); // => 8
```
## Literal
|type|example|
|---|---|
|int|123|
|float|0.5 .5|
|boolean|True true TRUE False  false FALSE|
|string|"aaa" 'aaa' 'aa\\'aa\\\\aa'|
|array|[1, 2, [3, 4]]|
|object|{'a': 1, 'b': {'c': 2}}|

## Operators and precedence
|Operator|example|
|---|---|
|!|!true|
|* / %|5 * 2|
|+ -|5 + 2|
|== != === !== |1 === 1|
|&&|true && false|
|\|\||true \|\| false|

## Magic function

|identifier|description|
|---|---|
|__add|Override the binary operator '+'.|
|__sub|Override the binary operator '-'.|
|__mul|Override the binary operator '*'.|
|__div|Override the binary operator '/'.|
|__mod|Override the binary operator '%'.|
|__or|Override the binary operator '\|\|'.|
|__and|Override the binary operator '&&'.|
|__equal|Override the binary operator '=='.|
|__not_equal|Override the binary operator '!='.|
|__equal_strict|Override the binary operator '==='.|
|__not_equal_strict|Override the binary operator '!=='.|
|__not|Override the unary operator '!'.|


```php
$evaluation = new Evaluation([
    '__add' => function (array $arguments) {
        return "$arguments[0] + $arguments[1]";
    }
]);
$result = $evaluation('1 + 2'); // => '1 + 2'
```

```php
$evaluation = new Evaluation([
    '*' => function (string $identify, array $arguments) {
        return 'call' . $identify . '(' . implode(', ', $arguments). ')';
    }
]);
$result = $evaluation('hoge(1, 2)'); // => 'call hoge(1, 2)'
```

## Parameter check

By passing an array, you can define the type of the argument.
It is also possible to express OR by separating with '|'.

|definition|description|
|---|---|
|numeric|Check with 'is_numeric'.|
|integer|Check with 'is_integer'.|
|float|Check with 'is_float'.|
|string|Check with 'is_string'.|
|bool|Check with 'is_bool'.|
|array|Check with 'is_array'.|
|object|Check with 'is_object'.|
|null|Check with 'is_null'.|
|mixed|All types are allowed.|

```php
$evaluation = new Evaluation([
    'repeat' => [
        'string, integer|null' => function (string $str, ?int $repeat) {
            return str_repeat($str, $repeat ?? 2);
        },
    ]
]);
$result = $evaluation("repeat('abc', 3)"); // => 'abcabcabc'
```

## Overload

Multiple patterns can be overloaded by registering them.

```php
$evaluation = new Evaluation([
    '__add' => [
        'string, string' => function (string $a, string $b) {
            return $a . $b;
        },
        'numeric, numeric' => function ($a, $b) {
            return $a + $b;
        },
    ]
]);
$result = $evaluation("'abc' + 'def'"); // => 'abcdef'
```

## Run-time error

|class|description|
|---|---|
|Errors\EvaluationError|Base class for errors that occur during evaluation.|
|Errors\SyntaxError|syntactic error.|
|Errors\UndefineFunctionError|Calling an undefined function.|
|Errors\ArgumentError|Error in parameter check.|

```php
use Naotake51\Evaluation\Evaluation;
use Naotake51\Evaluation\Errors\EvaluationError;

try {
    $evaluation = new Evaluation([
        'hoge' => function (array $arguments) {
            return 'hoge';
        },
    ]);
    $result = $evaluation("fuga()"); // => UndefineFunctionError
} catch (EvaluationError $e) {
    error_log($e->getMessage()); // => 'function fuga is not exists.'
}
```