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
 * @psalm-param rule|ruleWithValue $expectedValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function sameAs($expectedValue, string $error = 'It must be the same as VALUE.', callable ...$options): callable
{
    return __binop(fn($value, $expectedValue) => $value === $expectedValue, $expectedValue, $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param rule|ruleWithValue $expectedValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function notSameAs($expectedValue, string $error = 'It must not be the same as VALUE.', callable ...$options): callable
{
    return __binop(fn($value, $expectedValue) => $value !== $expectedValue, $expectedValue, $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param rule|ruleWithValue $expectedValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function equalTo($expectedValue, string $error = 'It must equal to VALUE.', callable ...$options): callable
{
    return __binop(fn($value, $expectedValue) => $value == $expectedValue, $expectedValue, $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param rule|ruleWithValue $expectedValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function notEqualTo($expectedValue, string $error = 'It must not equal to VALUE.', callable ...$options): callable
{
    return __binop(fn($value, $expectedValue) => $value != $expectedValue, $expectedValue, $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param rule|ruleWithValue $maxValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function lessThan($maxValue, string $error = 'It must be less than VALUE.', callable ...$options): callable
{
    return __binop(fn($value, $maxValue) => $value < $maxValue, $maxValue, $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param rule|ruleWithValue $minValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function notLessThan($minValue, string $error = 'It must be not less than VALUE.', callable ...$options): callable
{
    return __binop(fn($value, $minValue) => $value >= $minValue, $minValue, $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param rule|ruleWithValue $minValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function greaterThan($minValue, string $error = 'It must be greater than VALUE.', callable ...$options): callable
{
    return __binop(fn($value, $minValue) => $value > $minValue, $minValue, $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param rule|ruleWithValue $maxValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function notGreaterThan($maxValue, string $error = 'It must be not greater than VALUE.', callable ...$options): callable
{
    return __binop(fn($value, $maxValue) => $value <= $maxValue, $maxValue, $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param rule|ruleWithValue $maxValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function lessOrEqualTo(
    $maxValue,
    string $error = 'It must be less or equal to VALUE.',
    callable ...$options
): callable {
    return notGreaterThan($maxValue, $error, ...$options);
}

/**
 * @psalm-pure
 * @psalm-param rule|ruleWithValue $minValue
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function greaterOrEqualTo(
    $minValue,
    string $error = 'It must be greater or equal to VALUE.',
    callable ...$options
): callable {
    return notLessThan($minValue, $error, ...$options);
}
