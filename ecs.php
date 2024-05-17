<?php

use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;
use PhpCsFixer\Fixer\FunctionNotation\FunctionDeclarationFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->sets([
        SetList::ARRAY,
        SetList::COMMENTS,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
        SetList::PHPUNIT,
        SetList::STRICT,
        SetList::PSR_12,
    ]);

    $ecsConfig->ruleWithConfiguration(LineLengthSniff::class, ['absoluteLineLimit' => 120]);
    $ecsConfig->ruleWithConfiguration(ForbiddenFunctionsSniff::class, [
        'forbiddenFunctions' => ['dump' => \null, 'dd' => \null, 'var_dump' => \null, 'die' => \null],
    ]);
    $ecsConfig->ruleWithConfiguration(FunctionDeclarationFixer::class, ['closure_fn_spacing' => 'none']);
    $ecsConfig->ruleWithConfiguration(BlankLineBeforeStatementFixer::class, ['statements' => ['return']]);

    $ecsConfig->skip([
        ArrayListItemNewlineFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
        ExplicitStringVariableFixer::class,
        LineLengthSniff::class,
        StandaloneLineInMultilineArrayFixer::class,
        StrictComparisonFixer::class,
    ]);
};
