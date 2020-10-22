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
 * @psalm-suppress MissingClosureParamType
 */
function isEmail(string $error = 'It must have email format.', callable ...$options): callable
{
    return __unop(fn($value) => (bool)filter_var($value, FILTER_VALIDATE_EMAIL), $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function isUrl(string $error = 'It must have URL format.', callable ...$options): callable
{
    return __unop(fn($value) => (bool)filter_var($value, FILTER_VALIDATE_URL), $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 * @psalm-suppress MissingClosureParamType
 */
function isIp(string $error = 'It must have IP format.', callable ...$options): callable
{
    return __unop(fn($value) => (bool)filter_var($value, FILTER_VALIDATE_IP), $error, $options);
}

/**
 * @psalm-pure
 * @psalm-param string $regex
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function match(
    string $regex,
    string $error = 'It must match the regular expression REG_EXP.',
    callable ...$options
): callable {
    return static function($value, array $context, callable $option) use($regex, $error, $options): array {
        $option = __combineOptionGetterWithOptions($option, $options);

        if ($option('skipNullValues') && $value === null) {
            return [];
        }
        if (preg_match($regex, $value)) {
            return [];
        }

        return [
            $option('errorFormatter')($error, ['REG_EXP' => $regex])
        ];
    };
}
