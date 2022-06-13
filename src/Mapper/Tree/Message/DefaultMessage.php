<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

/** @api */
interface DefaultMessage
{
    public const TRANSLATIONS = [
        'Value {value} does not match expected {expected_value}.' => [
            'en' => 'Value {value} does not match expected {expected_value}.',
        ],
        'Value {value} does not match any of {allowed_values}.' => [
            'en' => 'Value {value} does not match any of {allowed_values}.',
        ],
        'Value {value} does not match any of {allowed_types}.' => [
            'en' => 'Value {value} does not match any of {allowed_types}.',
        ],
        'Value {value} does not match type {expected_type}.' => [
            'en' => 'Value {value} does not match type {expected_type}.',
        ],
        'Value {value} does not match float value {expected_value}.' => [
            'en' => 'Value {value} does not match float value {expected_value}.',
        ],
        'Value {value} does not match integer value {expected_value}.' => [
            'en' => 'Value {value} does not match integer value {expected_value}.',
        ],
        'Value {value} does not match string value {expected_value}.' => [
            'en' => 'Value {value} does not match string value {expected_value}.',
        ],
        'Invalid value {value}.' => [
            'en' => 'Invalid value {value}.',
        ],
        'Invalid value {value}: it must be an integer between {min} and {max}.' => [
            'en' => 'Invalid value {value}: it must be an integer between {min} and {max}.',
        ],
        'Invalid value {value}: it must be a negative integer.' => [
            'en' => 'Invalid value {value}: it must be a negative integer.',
        ],
        'Invalid value {value}: it must be a positive integer.' => [
            'en' => 'Invalid value {value}: it must be a positive integer.',
        ],
        'Invalid sequential key {key}, expected {expected}.' => [
            'en' => 'Invalid sequential key {key}, expected {expected}.',
        ],
        'Cannot cast {value} to {expected_type}.' => [
            'en' => 'Cannot cast {value} to {expected_type}.',
        ],
        'Cannot be empty.' => [
            'en' => 'Cannot be empty.',
        ],
        'Cannot be empty and must be filled with a valid string value.' => [
            'en' => 'Cannot be empty and must be filled with a valid string value.',
        ],
        'Cannot be empty and must be filled with a value matching type {expected_type}.' => [
            'en' => 'Cannot be empty and must be filled with a value matching type {expected_type}.',
        ],
        'Key {key} does not match type {expected_type}.' => [
            'en' => 'Key {key} does not match type {expected_type}.',
        ],
        'Value {value} does not match a valid date format.' => [
            'en' => 'Value {value} does not match a valid date format.',
        ],
        'Invalid class string {value}, it must be one of {expected_class_strings}.' => [
            'en' => 'Invalid class string {value}, it must be one of {expected_class_strings}.',
        ],
        'Invalid class string {value}, it must be a subtype of {expected_class_strings}.' => [
            'en' => 'Invalid class string {value}, it must be a subtype of {expected_class_strings}.',
        ],
        'Invalid class string {value}.' => [
            'en' => 'Invalid class string {value}.',
        ],
    ];
}
