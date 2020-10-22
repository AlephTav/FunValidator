<?php

namespace FunValidator;

/**
 * @psalm-type keyValue = array{0:mixed,1:mixed}
 * @psalm-type context = array{0:int,1:array}
 * @psalm-type options = array<string,mixed>
 * @psalm-type optionSetter = pure-callable(array):array
 * @psalm-type optionGetter = pure-callable(string,mixed=):mixed
 * @psalm-type optionSetters = optionSetter[]
 * @psalm-type rule = pure-callable(mixed,context,optionGetter):string[]
 * @psalm-type ruleWithValue = pure-callable(mixed,context,optionGetter):mixed
 * @psalm-type ruleForKeyValue = pure-callable(keyValue,context,optionGetter):string[]
 * @psalm-type ruleForIteration = pure-callable(int):rule
 */

/**
 * @psalm-pure
 * @psalm-param mixed $key
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function hasKey($key, string $error = 'Key KEY does not exists.', callable ...$options): callable
{
    return static function($array, array $context, callable $option) use ($key, $error, $options): array {
        if (is_string($key) || is_int($key)) {
            if (array_key_exists($key, __toArray($array))) {
                return [];
            }
        }

        $option = __combineOptionGetterWithOptions($option, $options);

        return [
            $option('errorFormatter')($error, ['KEY' => $key])
        ];
    };
}

/**
 * @psalm-pure
 * @psalm-param mixed $value
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function hasValue($value, string $error = 'Value VALUE does not exists.', callable ...$options): callable
{
    return static function($array, array $context, callable $option) use ($value, $error, $options): array {
        if (array_search($value, __toArray($array))) {
            return [];
        }

        $option = __combineOptionGetterWithOptions($option, $options);

        return [
            $option('errorFormatter')($error, ['VALUE' => $value])
        ];
    };
}

/**
 * @psalm-pure
 * @psalm-param mixed $key
 * @psalm-param mixed $value
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function hasKeyValue(
    $key,
    $value,
    string $error = 'Key/value pair [KEY => VALUE] does not exists.',
    callable ...$options
): callable {
    return static function($array, array $context, callable $option) use ($key, $value, $error, $options): array {
        if (is_string($key) || is_int($key)) {
            if (array_key_exists($key, $array = __toArray($array))) {
                if ($array[$key] == $value) {
                    return [];
                }
            }
        }

        $option = __combineOptionGetterWithOptions($option, $options);

        return [
            $option('errorFormatter')($error, ['KEY' => $key, 'VALUE' => $value])
        ];
    };
}

/**
 * @psalm-pure
 * @psalm-param mixed $firstKey
 * @psalm-param mixed $secondKey
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function valuesWithKeysEqual(
    $firstKey,
    $secondKey,
    string $error = 'Values must be equal to.',
    callable ...$options
): callable {
    return static function($array, array $context, callable $option)
        use($firstKey, $secondKey, $error, $options): array {
        if ((is_string($firstKey) || is_int($firstKey)) && (is_string($secondKey) || is_int($secondKey))) {
            $array = __toArray($array);
            $flag = ($array[$firstKey] ?? null) == ($array[$secondKey] ?? null);
        } else {
            $flag = false;
        }

        if ($flag) {
            return [];
        }

        $option = __combineOptionGetterWithOptions($option, $options);

        return [
            $option('errorFormatter')($error)
        ];
    };
}

/**
 * @psalm-pure
 * @psalm-param mixed $key
 * @psalm-param pure-callable(mixed):mixed|null $valueWithKey
 * @psalm-return pure-callable(mixed):mixed
 */
function valueWithKey($key, callable $valueWithKey = null): callable
{
    return static function($array) use($key, $valueWithKey) {
        if (is_string($key) || is_int($key)) {
            $value = __toArray($array)[$key] ?? null;
        } else {
            $value = null;
        }
        if ($valueWithKey) {
            return $valueWithKey($value);
        }
        return $value;
    };
}

/**
 * @psalm-pure
 * @psalm-param mixed $key
 * @psalm-param rule $rule
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function onKey($key, callable $rule, string $error = '', callable ...$options): callable
{
    return static function($array, array $context, callable $option) use($key, $rule, $error, $options): array {
        if (is_string($key) || is_int($key)) {
            $value = __toArray($array)[$key] ?? null;
        } else {
            $value = null;
        }

        $option = __combineOptionGetterWithOptions($option, $options);

        $errors = $rule($value, __addToContext($context, $value), $option);
        if (!$errors) {
            return [];
        }

        if ($error !== '') {
            $errors = [
                $option('errorFormatter')($error, ['KEY' => $key])
            ];
        }

        return $errors;
    };
}
