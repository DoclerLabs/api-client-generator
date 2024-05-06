<?php

use PhpCsFixer\Config;

$rules = [
    '@PSR2'                                     => true,
    'array_syntax'                              => ['syntax' => 'short'],
    'multiline_whitespace_before_semicolons'    => ['strategy' => 'no_multi_line'],
    'no_blank_lines_after_class_opening'        => true,
    'echo_tag_syntax'                           => ['format' => 'long'],
    'no_unused_imports'                         => true,
    'blank_line_after_opening_tag'              => true,
    'not_operator_with_successor_space'         => false,
    'no_useless_else'                           => true,
    'ordered_imports'                           => ['sort_algorithm' => 'alpha'],
    'class_attributes_separation'               => [
        'elements' => [
            'const'    => 'one',
            'method'   => 'one',
            'property' => 'one',
        ],
    ],
    'blank_line_before_statement'               => [
        'statements' => ['break', 'continue', 'return', 'try'],
    ],
    'no_alternative_syntax'                     => true,
    'phpdoc_add_missing_param_annotation'       => true,
    'phpdoc_align'                              => ['align' => 'vertical'],
    'phpdoc_indent'                             => true,
    'phpdoc_no_package'                         => true,
    'phpdoc_order'                              => true,
    'phpdoc_separation'                         => true,
    'phpdoc_single_line_var_spacing'            => true,
    'phpdoc_trim'                               => true,
    'phpdoc_var_without_name'                   => true,
    'phpdoc_to_comment'                         => false,
    'phpdoc_scalar'                             => [
        'types' => ['boolean', 'double', 'integer', 'real', 'str'],
    ],
    'single_quote'                              => true,
    'ternary_operator_spaces'                   => true,
    'trim_array_spaces'                         => true,
    'no_leading_import_slash'                   => true,
    'declare_strict_types'                      => true,
    'single_line_after_imports'                 => true,
    'not_operator_with_space'                   => false,
    'no_spaces_inside_parenthesis'              => true,
    'unary_operator_spaces'                     => true,
    'return_type_declaration'                   => ['space_before' => 'none'],
    'braces'                                    => ['allow_single_line_closure' => true],
    'binary_operator_spaces'                    => ['operators' => ['=>' => 'align_single_space_minimal', '=' => 'align_single_space_minimal']],
    'no_superfluous_phpdoc_tags'                => true,
    'no_empty_phpdoc'                           => true,
    'no_extra_blank_lines'                      => true,
];

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setIndent('    ')
    ->setUsingCache(false);
