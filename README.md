# 概要
naotake51/evaluationは、簡易的に式評価モジュールを作成するためのComposerパッケージです。
自前の関数を登録して、式（文字列）を評価する事ができます。

# 使い方

```
$evaluation = new Evaluation([
    'square' => function (array $arguments) {
        return $arguments[0] * $arguments[0];
    }
]);
$result = $evaluation('square(2) + square(2)'); // => 8
```

# マジック関数

|識別子|説明|
|---|---|
|__add|2項演算子'+'をオーバーライドします。|
|__sub|2項演算子'-'をオーバーライドします。|
|__mul|2項演算子'*'をオーバーライドします。|
|__div|2項演算子'/'をオーバーライドします。|
|__mod|2項演算子'%'をオーバーライドします。|
|*|定義されていない関数の呼び出しがあった場合に呼び出されます。|


```
$evaluation = new Evaluation([
    '__add' => function (array $arguments) {
        return "$arguments[0] + $arguments[1]";
    }
]);
$result = $evaluation('1 + 2'); // => '1 + 2'
```

```
$evaluation = new Evaluation([
    '*' => function (string $identify, array $arguments) {
        return 'call' . $identify . '(' . implode(', ', $arguments). ')';
    }
]);
$result = $evaluation('hoge(1, 2)'); // => 'call hoge(1, 2)'
```

