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
 * @psalm-param pure-callable(string,array):string $formatter
 * @psalm-return optionSetter
 */
function withErrorFormatter(callable $formatter): callable
{
    return static function(array $options) use ($formatter): array {
        $options['errorFormatter'] = $formatter;
        return $options;
    };
}

/**
 * @psalm-pure
 * @psalm-return optionSetter
 */
function withSkippingNullValues(): callable
{
    return static function(array $options): array {
        $options['skipNullValues'] = true;
        return $options;
    };
}

/**
 * @psalm-pure
 * @psalm-return optionSetter
 */
function withoutSkippingNullValues(): callable
{
    return static function(array $options): array {
        $options['skipNullValues'] = false;
        return $options;
    };
}

/**
 * @psalm-pure
 * @psalm-return optionSetter
 */
function withStoppingOnFirstError(): callable
{
    return static function(array $options): array {
        $options['stopOnFirstError'] = true;
        return $options;
    };
}

/**
 * @psalm-pure
 * @psalm-return optionSetter
 */
function withoutStoppingOnFirstError(): callable
{
    return static function(array $options): array {
        $options['stopOnFirstError'] = false;
        return $options;
    };
}

/**
 * @psalm-pure
 * @psalm-param array<pure-callable(array):array> $optionSetters
 * @psalm-return pure-callable(mixed,pure-callable(mixed,array,pure-callable(string,mixed=):mixed):array):array
 */
function buildValidator(callable ...$optionSetters): callable
{
    $defaultOptions = [
        'errorFormatter' => __NAMESPACE__ . '\__errorFormatter',
        'skipNullValues' => true,
        'stopOnFirstError' => false
    ];

    $options = __combineOptions($optionSetters, $defaultOptions);

    return static function($value, callable $rule) use($options): array {
        return validate($value, $rule, $options);
    };
}