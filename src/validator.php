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
 * @psalm-param ruleWithValue $rule
 * @psalm-return ruleWithValue
 */
function inPrevContext(callable $rule): callable
{
    return static function($value, array $context, callable $option) use($rule) {
        $value = $context[1][--$context[0]];
        return $rule($value, $context, $option);
    };
}

/**
 * @psalm-pure
 * @psalm-param ruleWithValue $rule
 * @psalm-return ruleWithValue
 */
function inNextContext(callable $rule): callable
{
    return static function($value, array $context, callable $option) use($rule) {
        $value = $context[1][++$context[0]];
        return $rule($value, $context, $option);
    };
}

/**
 * @psalm-pure
 * @psalm-param mixed $value
 * @psalm-param rule $rule
 * @psalm-param options $options
 * @psalm-return string[]
 */
function validate($value, callable $rule, array $options = []): array
{
    return $rule($value, [0, [$value]], __optionGetter([], $options));
}