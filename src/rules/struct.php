<?php

namespace FunValidator;

use Generator;

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
 * @psalm-type ruleForStruct = callable(array,int,context,optionGetter):Generator
 */

/**
 * @psalm-param rule $rule
 * @psalm-return ruleForStruct
 */
function item(callable $rule): callable
{
    return static function(array $struct, int $pos, array $context, callable $option) use($rule): Generator {
        $errors = $rule($struct[$pos], $context, $option);
        if (!$errors) {
            yield ($pos + 1);
        }
        return $pos;
    };
}

/**
 * @psalm-param ruleForStruct[] $rules
 * @psalm-return ruleForStruct
 */
function alternative(array $rules): callable
{
    return static function(array $struct, int $pos, array $context, callable $option) use($rules): Generator {
        foreach ($rules as $rule) {
            foreach ($rule($struct, $pos, $context, $option) as $i) {
                yield $i;
            }
        }
        return $pos;
    };
}

/**
 * @psalm-param ruleForStruct $rule
 * @psalm-param int $min
 * @psalm-param int $max
 * @psalm-return ruleForStruct
 */
function repeat(callable $rule, int $min = 0, int $max = -1): callable
{
    $min = $min < 0 ? 0 : $min;
    return static function(array $struct, int $pos, array $context, callable $option) use($rule, $min, $max): Generator {
        $max = $max < 0 ? count($struct) : $max;
        if ($max < $min) {
            return $pos;
        }

        if ($min === 0) {
            yield $pos;
        }

        $rec = function(int $min, int $max, callable $rule, array $struct, int $pos, array $context, callable $option)
            use(&$rec): Generator {
            if ($min > 1) {
                foreach ($rule($struct, $pos, $context, $option) as $i) {
                    yield from $rec($min - 1, $max - 1, $rule, $struct, $i, $context, $option);
                }
            } else {
                $i = $pos;
                while ($max > 0) {
                    foreach ($rule($struct, $i, $context, $option) as $i) {
                        yield $i;
                    }
                    --$max;
                }
            }
        };

        yield from $rec($min, $max, $rule, $struct, $pos, $context, $option);

        return $pos;
    };
}

/**
 * @psalm-param ruleForStruct[] $rules
 * @psalm-return ruleForStruct
 */
function sequence(array $rules): callable
{
    return static function(array $struct, int $pos, array $context, callable $option) use($rules): iterable {
        $rec = function(array $rules, array $struct, int $pos, array $context, callable $option) use(&$rec): iterable {
            $rule = array_shift($rules);
            if (!$rule) {
                return;
            }
            if ($rules) {
                foreach ($rule($struct, $pos, $struct, $option) as $i) {
                    yield from $rec($rules, $struct, $i, $context, $option);
                }
            } else {
                foreach ($rule($struct, $pos, $struct, $option) as $i) {
                    yield $i;
                }
            }
        };

        yield from $rec($rules, $struct, $pos, $context, $option);

        return $pos;
    };
}

/**
 * @psalm-pure
 * @psalm-param ruleForStruct $rule
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress InvalidReturnType
 * @psalm-suppress InvalidReturnStatement
 */
function struct(callable $rule, string $error = 'Invalid structure.', callable ... $options): callable
{
    return static function($array, array $context, callable $option) use($rule, $error, $options): array {
        $option = __combineOptionGetterWithOptions($option, $options);

        $struct = [];
        foreach (__toArray($array) as $key => $value) {
            $struct[] = [$key, $value];
        }

        $count = count($struct);
        foreach ($rule($struct, 0, $context, $option) as $i) {
            if ($i >= $count) {
                return [];
            }
        }

        return [
            $option('errorFormatter')($error)
        ];
    };
}