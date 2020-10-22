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
 * @spalm-param optionSetters $optionSetters
 * @psalm-param options $defaultOptions
 * @psalm-return options
 */
function __combineOptions(array $optionSetters, array $defaultOptions = []): array
{
    $options = $defaultOptions;
    foreach ($optionSetters as $option) {
        $options = $option($options);
    }
    return $options;
}

/**
 * @psalm-pure
 * @psalm-param optionSetters $optionSetters
 * @psalm-param options $defaultOptions
 * @psalm-return optionGetter
 */
function __optionGetter(array $optionSetters, array $defaultOptions = []): callable
{
    $options = __combineOptions($optionSetters, $defaultOptions);
    return static function(string $option, $default = null) use($options) {
        return $options[$option] ?? $default;
    };
}

/**
 * @psalm-pure
 * @psalm-param optionGetter $firstOptionGetter
 * @psalm-param optionSetters $optionSetters
 * @psalm-return optionGetter
 */
function __combineOptionGetterWithOptions(callable $firstOptionGetter, array $optionSetters): callable
{
    $secondOptionGetter = __optionGetter($optionSetters);
    return static function(string $option, $default = null) use($firstOptionGetter, $secondOptionGetter) {
        return $secondOptionGetter($option) ?? $firstOptionGetter($option, $default);
    };
}

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param array $replacePairs
 * @psalm-return string
 */
function __errorFormatter(string $error, array $replacePairs = []): string
{
    return strtr($error, $replacePairs);
}

/**
 * @psalm-pure
 * @psalm-param context $context
 * @psalm-param mixed $value
 * @psalm-return context
 */
function __addToContext(array $context, $value): array
{
    ++$context[0];
    $context[1] = array_merge($context[1], [$value]);
    return $context;
}

/**
 * @psalm-pure
 * @psalm-param mixed $value
 * @psalm-return array
 */
function __toArray($value): array
{
    if (is_array($value)) {
        return $value;
    }
    if (is_object($value)) {
        return (array)$value;
    }
    if ($value instanceof \Traversable) {
        return iterator_to_array($value, true);
    }
    return [];
}

/**
 * @psalm-pure
 * @psalm-param pure-callable(mixed):bool $op
 * @psalm-param string $error
 * @psalm-param optionSetters $optionSetters
 * @psalm-return rule
 */
function __unop(callable $op, string $error, array $optionSetters): callable
{
    return static function($value, array $context, callable $option) use($op, $error, $optionSetters): array {
        $option = __combineOptionGetterWithOptions($option, $optionSetters);

        if ($option('skipNullValues') && $value === null) {
            return [];
        }
        if ($op($value)) {
            return [];
        }

        return [
            $option('errorFormatter')($error)
        ];
    };
}

/**
 * @psalm-pure
 * @psalm-param pure-callable(mixed,mixed):bool $op
 * @psalm-param pure-callable(pure-callable(mixed,mixed):bool,context,optionGetter):mixed|mixed $secondOperand
 * @psalm-param string $error
 * @psalm-param optionSetters $optionSetters
 * @psalm-return rule
 */
function __binop(callable $op, $secondOperand, string $error, array $optionSetters): callable
{
    return static function($firstOperand, array $context, callable $option)
        use ($op, $secondOperand, $error, $optionSetters): array {
        $option = __combineOptionGetterWithOptions($option, $optionSetters);

        if ($option('skipNullValues') && $firstOperand === null) {
            return [];
        }
        if (is_callable($secondOperand)) {
            $secondOperand = $secondOperand($firstOperand, $context, $option);
        }
        if ($op($firstOperand, $secondOperand)) {
            return [];
        }

        return [
            $option('errorFormatter')($error, ['VALUE' => $secondOperand])
        ];
    };
}