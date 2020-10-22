<?php

require_once __DIR__ . '/src/bootstrap.php';

use function FunValidator\{
    buildValidator,
    withSkippingNullValues,
    withoutSkippingNullValues,
    withStoppingOnFirstError,
    withoutStoppingOnFirstError,
    inPrevContext,
    inNextContext,
    _and,
    _or,
    _if,
    _value,
    _key,
    _true,
    _false,
    equalTo,
    lessThan,
    greaterThan,
    isRequired,
    isString,
    isInteger,
    isBoolean,
    isEmail,
    hasKey,
    onKey,
    valueWithKey,
    valuesWithKeysEqual,
    iterate,
    struct,
    sequence,
    alternative,
    repeat,
    item
};

$validate = buildValidator(
    withSkippingNullValues(),
    withoutStoppingOnFirstError()
);

$rules = _and([
    onKey('firstName', _and([
        isRequired(),
        isString()
    ], 'First name must be not empty string.')),
    onKey('lastName', isString(), 'Last name must be a string.'),
    onKey('email', _and([
        isRequired('Email must not be empty.'),
        isEmail('Email has invalid format.')
    ])),
    onKey('age', _or([
        isInteger(),
        isString()
    ], 'Age must be a string or an integer.')),
    _if(hasKey('password'),
        valuesWithKeysEqual(
            'password',
            'passwordConfirmation',
            'Password must equal to password confirmation.'
        )
    )
]);

$errors = $validate(
    [
        'firstName' => 'Vasya',
        'lastName' => 'Pupkin',
        'email' => 'pupkin@gmail.com',
        'age' => 30,
        'password' => 'abc',
        'passwordConfirmation' => 'abc'
    ],
    $rules
);

print_r($errors);

$errors = $validate(
    [
        'lastName' => 'Pupkin',
        'email' => 'pupkingmail.com',
        'age' => true,
        'password' => 'abcd',
        'passwordConfirmation' => 'abc'
    ],
    $rules
);

print_r($errors);

$errors = $validate(
    [
        'a0' => [
            'a10' => 1,
            'a11' => 2
        ],
        'b0' => [
            'b10' => 3,
            'b11' => 2,
            'b12' => 1
        ],
        'c0' => 1
    ],
    _if(
        _and([
            hasKey('a0'),
            hasKey('b0')
        ]),
        onKey('b0',
            onKey('b12',
                lessThan(
                    inPrevContext(inPrevContext(valueWithKey('a0', valueWithKey('a10'))))
                )
            ),
            'Element [b0][b12] must be less than [a0][a10].'
        ),
        onKey('b0',
            onKey('b10',
                _and([
                    greaterThan(inPrevContext(inPrevContext(valueWithKey('c0')))),
                    greaterThan(inPrevContext(valueWithKey('b11')))
                ])
            ),
            'Element [b0][b10] must be greater than [c0] and [b0][b11].',
            withoutSkippingNullValues()
        )
    )
);

print_r($errors);

$errors = $validate(
    [0, 1, 2, 3, 4, 5],
    iterate(
        fn(int $i) => _value(equalTo(inPrevContext(_key()))),
        0,
        0,
        'Keys must equal to values.'
    )
);

print_r($errors);

$errors = $validate(
    [1, 2, 4, 8, 16, 'a' => 32, 'b' => 64, 'c' => 128],
    _and([
        iterate(
            fn(int $i) => _value(equalTo(1 << $i)),
            0,
            0,
            'Array must contain consecutive powers of 2.'
        ),
        iterate(
            fn(int $i) => _and([
                _key(equalTo(chr(ord('a') + $i - 5))),
                _value(equalTo(1 << $i))
            ]),
            3,
            5,
            'Last three items must have keys that are successive letters.'
        ),
    ], '', withStoppingOnFirstError())
);

print_r($errors);

$errors = $validate(
    [1, 2, true, 2, false, 'b', 'c', 1, 2, 3],
    struct(
        repeat(
            alternative([
                sequence([
                    repeat(item(_value(isString())), 0, 2),
                    repeat(item(_value(isInteger())), 2, 3),
                    repeat(item(_value(isBoolean())), 0, 1)
                ]),
                sequence([
                    item(_value(isInteger())),
                    item(_value(isBoolean()))
                ])
            ]),
            0, 3
        )
    )
);

print_r($errors);