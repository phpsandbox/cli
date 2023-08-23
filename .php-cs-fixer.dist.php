<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        './',
        'app',
        'config',
        'tests',
    ])
    ->exclude([
        'vendors',
        'bootstrap',
    ]);

$rules = [
    '@PSR2'             => true,
    'array_indentation' => true,
    'array_syntax'      => [
        'syntax' => 'short',
    ],
    'binary_operator_spaces' => [
        'operators' => ['=>' => 'align', '=' => 'single_space'],
    ],
    'blank_line_after_namespace'   => true,
    'blank_line_after_opening_tag' => true,
    'blank_line_before_statement'  => [
        'statements' => [
            'return',
            'throw',
        ],
    ],
    'cast_spaces'                 => true,
    'class_attributes_separation' => [
        'elements' => [
            'method'   => 'one',
            'property' => 'one',
        ],
    ],
    'combine_consecutive_issets' => true,
    'combine_consecutive_unsets' => true,
    'compact_nullable_typehint'  => true,
    'concat_space'               => [
        'spacing' => 'one',
    ],
    'declare_equal_normalize' => true,
    'elseif'                  => true,
    'list_syntax'             => [
        'syntax' => 'short',
    ],
    'lowercase_cast'                             => true,
    'magic_constant_casing'                      => true,
    'method_chaining_indentation'                => true,
    'native_function_casing'                     => true,
    'native_function_type_declaration_casing'    => true,
    'new_with_braces'                            => true,
    'no_blank_lines_after_class_opening'         => true,
    'no_blank_lines_after_phpdoc'                => true,
    'no_empty_phpdoc'                            => true,
    'no_empty_statement'                         => true,
    'no_extra_blank_lines'                       => true,
    'no_leading_import_slash'                    => true,
    'no_leading_namespace_whitespace'            => true,
    'multiline_whitespace_before_semicolons'     => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'no_superfluous_elseif'                      => true,
    'no_trailing_comma_in_singleline'            => true,
    'no_unneeded_control_parentheses'            => true,
    'no_unneeded_curly_braces'                   => true,
    'no_unused_imports'                          => true,
    'no_useless_else'                            => true,
    'no_useless_return'                          => true,
    'no_whitespace_in_blank_line'                => true,
    'normalize_index_brace'                      => true,
    'not_operator_with_successor_space'          => true,
    'ordered_imports'                            => true,
    'ordered_interfaces'                         => true,
    'php_unit_construct'                         => [
        'assertions' => [
            'assertEquals',
            'assertNotEquals',
            'assertNotSame',
            'assertSame',
        ],
    ],
    'php_unit_dedicate_assert' => [
        'target' => 'newest',
    ],
    'php_unit_method_casing' => [
        'case' => 'camel_case',
    ],
    'php_unit_set_up_tear_down_visibility' => true,
    'php_unit_test_annotation'             => [
        'style' => 'annotation',
    ],
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'this',
    ],
    'phpdoc_no_package'                  => true,
    'phpdoc_scalar'                      => true,
    'phpdoc_trim'                        => true,
    'return_assignment'                  => true,
    'return_type_declaration'            => true,
    'semicolon_after_instruction'        => true,
    'short_scalar_cast'                  => true,
    'single_blank_line_at_eof'           => true,
    'blank_lines_before_namespace'       => true,
    'single_line_after_imports'          => true,
    'single_quote'                       => true,
    'space_after_semicolon'              => true,
    'standardize_not_equals'             => true,
    'ternary_operator_spaces'            => true,
    'ternary_to_null_coalescing'         => true,
    'trailing_comma_in_multiline'        => true,
    'trim_array_spaces'                  => true,
    'unary_operator_spaces'              => true,
    'void_return'                        => true,
    'yoda_style'                         => [
        'equal'            => false,
        'identical'        => false,
        'less_and_greater' => false,
    ],
];

return (new Config())
    ->setFinder($finder)
    ->setRules(array_merge($rules, []))
    ->setRiskyAllowed(true);
