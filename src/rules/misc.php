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
 * @psalm-type ruleForKeyValue = pure-callable(keyValue,context,optionGetter):mixed
 * @psalm-type ruleForIteration = pure-callable(int):rule
 */

/**
 * @psalm-pure
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function isRequired(string $error = 'It must be required.', callable ...$options): callable
{
    return static function($value, array $context, callable $option) use($error, $options): array {
        if ($value === null) {
            $flag = false;
        } elseif (is_resource($value) || is_object($value)) {
            $flag = true;
        } elseif (is_array($value)) {
            $flag = !empty($value);
        } elseif (is_string($value)) {
            $flag = $value !== '';
        } elseif (is_bool($value)) {
            $flag = $value;
        } else {
            $flag = $value != 0;
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
