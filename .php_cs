<?php
$finder = Symfony\CS\Finder\DefaultFinder::create()
->in(__DIR__)
->exclude('tests/fixtures/exports');

$config = Symfony\CS\Config\Config::create()
->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
->finder($finder)
->fixers(
    [
        'blankline_after_open_tag',
        'extra_empty_lines',
        'no_blank_lines_after_class_opening',
        'no_empty_lines_after_phpdocs',
        'operators_spaces',
        'duplicate_semicolon',
        'namespace_no_leading_whitespace',
        'phpdoc_indent',
        'phpdoc_no_empty_return',
        'phpdoc_no_package',
        'phpdoc_params',
        'phpdoc_separation',
        'phpdoc_to_comment',
        'phpdoc_trim',
        'phpdoc_var_without_name',
        'remove_leading_slash_use',
        '-return',
        'single_array_no_trailing_comma',
        'single_quote',
        'spaces_before_semicolon',
        '-spaces_cast',
        'standardize_not_equal',
        'ternary_spaces',
        'whitespacy_lines',
        'short_array_syntax',
        '-ordered_use',
        '-unary_operators_spaces',
        '-single_line_after_imports',
        '-remove_lines_between_uses',
    ]
);
return $config;
