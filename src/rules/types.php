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
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function isNull(string $error = 'It must be null.', callable ...$options): callable
{
    return __unop('is_null', $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function isBoolean(string $error = 'It must be a boolean.', callable ...$options): callable
{
    return __unop('is_bool', $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function isInteger(string $error = 'It must be an integer.', callable ...$options): callable
{
    return __unop('is_int', $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function isFloat(string $error = 'It must be a float.', callable ...$options): callable
{
    return __unop('is_float', $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function isString(string $error = 'It must be a string.', callable ...$options): callable
{
    return __unop('is_string', $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function isArray(string $error = 'It must be an array.', callable ...$options): callable
{
    return __unop('is_array', $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function isIterable(string $error = 'It must be an iterable object.', callable ...$options): callable
{
    return __unop('is_iterable', $error, $options);
}