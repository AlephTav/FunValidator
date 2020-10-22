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
 * @template T as rule|null
 * @psalm-param T $rule
 * @psalm-return (T is null ? pure-callable(keyValue):mixed : ruleForKeyValue)
 */
function _key(callable $rule = null): callable
{
    if ($rule === null) {
        return static function(array $keyValuePair) {
            return $keyValuePair[0] ?? null;
        };
    }
    return static function(array $keyValuePair, array $context, callable $option) use($rule): array {
        $value = $keyValuePair[0] ?? null;
        return $rule($value, __addToContext($context, $value), $option);
    };
}

/**
 * @psalm-pure
 * @template T as rule|null
 * @psalm-param T $rule
 * @psalm-return (T is null ? pure-callable(keyValue):mixed : ruleForKeyValue)
 */
function _value(callable $rule = null): callable
{
    if ($rule === null) {
        return static function(array $keyValuePair) {
            return $keyValuePair[1] ?? null;
        };
    }
    return static function(array $keyValuePair, array $context, callable $option) use($rule): array {
        $value = $keyValuePair[1] ?? null;
        return $rule($value, __addToContext($context, $value), $option);
    };
}

/**
 * @psalm-pure
 * @psalm-param ruleForIteration $iterationRule
 * @psalm-param int $count
 * @psalm-param int $skipFirst
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function iterate(
    callable $iterationRule,
    int $count = 0,
    int $skipFirst = 0,
    string $error = 'Invalid structure.',
    callable ...$options
): callable {
    return static function($iterator, $context, callable $option)
    use ($count, $skipFirst, $error, $iterationRule, $options): array {
        $option = __combineOptionGetterWithOptions($option, $options);
        $stopOnFirstError = $option('stopOnFirstError');

        $fixedCount = $count > 0;
        $skipped = $skipFirst;
        $total = $count;
        $errors = [];
        $index = 0;
        foreach (__toArray($iterator) as $key => $value) {
            if ($skipped <= 0) {
                $value = [$key, $value];
                $errors = array_merge(
                    $errors,
                    $iterationRule($index)($value, __addToContext($context, $value), $option)
                );
                if ($stopOnFirstError && $errors) {
                    break;
                }
                if ($fixedCount) {
                    --$total;
                    if ($total <= 0) {
                        break;
                    }
                }
            } else {
                --$skipped;
            }
            ++$index;
        }

        if (!$errors && $total <= 0) {
            return [];
        }

        if ($error !== '') {
            $errors = [
                $option('errorFormatter')($error)
            ];
        }

        return $errors;
    };
}
