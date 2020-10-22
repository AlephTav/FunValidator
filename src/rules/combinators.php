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
 * @psalm-param rule|ruleWithValue $condition
 * @psalm-param rule $then
 * @psalm-param rule|null $else
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function _if(
    callable $condition,
    callable $then,
    callable $else = null,
    string $error = '',
    callable ...$options
): callable {
    return static function($value, array $context, callable $option)
    use ($condition, $then, $else, $error, $options): array {
        $option = __combineOptionGetterWithOptions($option, $options);

        $condition($value, $context, $option) ?
            $errors = ($else ?? _true())($value, $context, $option) : $errors = $then($value, $context, $option);

        if (!$errors) {
            return [];
        }

        if ($error !== '') {
            $errors[] = $option('errorFormatter')($error, ['VALUE' => $value]);
        }

        return $errors;
    };
}

/**
 * @psalm-pure
 * @psalm-param rule[] $rules
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function _and(array $rules, string $error = '', callable ... $options): callable
{
    return static function($value, array $context, callable $option) use ($rules, $error, $options): array {
        $option = __combineOptionGetterWithOptions($option, $options);

        $stopOnFirstError = $option('stopOnFirstError');

        $errors = [];
        if ($stopOnFirstError) {
            foreach ($rules as $rule) {
                $errors = $rule($value, $context, $option);
                if ($errors) {
                    break;
                }
            }
        } else {
            foreach ($rules as $rule) {
                $errors = array_merge($errors, $rule($value, $context, $option));
            }
        }

        if (!$errors) {
            return [];
        }

        if ($error !== '') {
            $errors = [
                $option('errorFormatter')($error, ['VALUE' => $value])
            ];
        }

        return $errors;
    };
}

/**
 * @psalm-pure
 * @psalm-param rule[] $rules
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function _or(array $rules, string $error = '', callable ...$options): callable
{
    return static function($value, array $context, callable $option) use ($rules, $error, $options): array {
        $option = __combineOptionGetterWithOptions($option, $options);

        $errors = [];
        foreach ($rules as $rule) {
            $result = $rule($value, $context, $option);
            if (!$result) {
                $errors = [];
                break;
            }
            $errors = array_merge($errors, $result);
        }

        if (!$errors) {
            return [];
        }

        if ($error !== '') {
            $errors = [
                $option('errorFormatter')($error, ['VALUE' => $value])
            ];
        } elseif ($option('stopOnFirstError')) {
            $errors = [array_shift($errors)];
        }

        return $errors;
    };
}

/**
 * @psalm-pure
 * @psalm-param rule $condition
 * @psalm-param string $error
 * @psalm-param optionSetters $options
 * @psalm-return rule
 */
function _not(callable $condition, string $error = 'Invalid value.', callable ...$options): callable
{
    return static function($value, array $context, callable $option) use($condition, $error, $options): array {
        $option = __combineOptionGetterWithOptions($option, $options);

        $errors = $condition($value, $context, $option);

        if ($errors) {
            return [];
        }

        if ($error !== '') {
            $errors[] = $option('errorFormatter')($error, ['VALUE' => $value]);
        }

        return $errors;
    };
}
