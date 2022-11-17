<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

/** @internal */
interface DefaultMessage
{
    public const TRANSLATIONS = [
        'Value {source_value} does not match any of {allowed_values}.' => [
            'en' => 'Value {source_value} does not match any of {allowed_values}.',
        ],
        'Value {source_value} does not match any of {allowed_types}.' => [
            'en' => 'Value {source_value} does not match any of {allowed_types}.',
        ],
        'Cannot be empty and must be filled with a value matching any of {allowed_types}.' => [
            'en' => 'Cannot be empty and must be filled with a value matching any of {allowed_types}.',
        ],
        'Value {source_value} does not match type {expected_type}.' => [
            'en' => 'Value {source_value} does not match type {expected_type}.',
        ],
        'Value {source_value} does not match {expected_value}.' => [
            'en' => 'Value {source_value} does not match {expected_value}.',
        ],
        'Value {source_value} does not match boolean value {expected_value}.' => [
            'en' => 'Value {source_value} does not match boolean value {expected_value}.',
        ],
        'Value {source_value} does not match float value {expected_value}.' => [
            'en' => 'Value {source_value} does not match float value {expected_value}.',
        ],
        'Value {source_value} does not match integer value {expected_value}.' => [
            'en' => 'Value {source_value} does not match integer value {expected_value}.',
        ],
        'Value {source_value} does not match string value {expected_value}.' => [
            'en' => 'Value {source_value} does not match string value {expected_value}.',
        ],
        'Value {source_value} is not a valid boolean.' => [
            'en' => 'Value {source_value} is not a valid boolean.',
        ],
        'Value {source_value} is not a valid float.' => [
            'en' => 'Value {source_value} is not a valid float.',
        ],
        'Value {source_value} is not a valid integer.' => [
            'en' => 'Value {source_value} is not a valid integer.',
        ],
        'Value {source_value} is not a valid string.' => [
            'en' => 'Value {source_value} is not a valid string.',
        ],
        'Value {source_value} is not a valid negative integer.' => [
            'en' => 'Value {source_value} is not a valid negative integer.',
        ],
        'Value {source_value} is not a valid positive integer.' => [
            'en' => 'Value {source_value} is not a valid positive integer.',
        ],
        'Value {source_value} is not a valid non-empty string.' => [
            'en' => 'Value {source_value} is not a valid non-empty string.',
        ],
        'Value {source_value} is not a valid numeric string.' => [
            'en' => 'Value {source_value} is not a valid numeric string.',
        ],
        'Value {source_value} is not a valid integer between {min} and {max}.' => [
            'en' => 'Value {source_value} is not a valid integer between {min} and {max}.',
        ],
        'Value {source_value} is not a valid timezone.' => [
            'en' => 'Value {source_value} is not a valid timezone.',
        ],
        'Value {source_value} is not a valid class string.' => [
            'en' => 'Value {source_value} is not a valid class string.',
        ],
        'Value {source_value} is not a valid class string of `{expected_class_type}`.' => [
            'en' => 'Value {source_value} is not a valid class string of `{expected_class_type}`.',
        ],
        'Invalid value {source_value}.' => [
            'en' => 'Invalid value {source_value}.',
        ],
        'Invalid sequential key {key}, expected {expected}.' => [
            'en' => 'Invalid sequential key {key}, expected {expected}.',
        ],
        'Cannot be empty.' => [
            'en' => 'Cannot be empty.',
        ],
        'Cannot be empty and must be filled with a value matching type {expected_type}.' => [
            'en' => 'Cannot be empty and must be filled with a value matching type {expected_type}.',
        ],
        'Key {key} does not match type {expected_type}.' => [
            'en' => 'Key {key} does not match type {expected_type}.',
        ],
        'Value {source_value} does not match a valid date format.' => [
            'en' => 'Value {source_value} does not match a valid date format.',
        ],
        'Value {source_value} does not match any of the following formats: {formats}.' => [
            'en' => 'Value {source_value} does not match any of the following formats: {formats}.',
        ],
    ];
}
