<?php

declare( strict_types=1 );

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector;
use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function ( RectorConfig $config ): void {
    $config->sets( [
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::NAMING,
        SetList::PRIVATIZATION,
        LevelSetList::UP_TO_PHP_82,
    ] );

    $config->rule(InlineConstructorDefaultToPropertyRector::class);

    $config->fileExtensions( ['php'] );
    $config->importNames();
    $config->removeUnusedImports();
    $config->importShortClasses( false );
    $config->parallel();

    $config->paths( [
        __DIR__.'/src',
        __DIR__.'/tests',
    ] );

    $config->skip( [
        UnSpreadOperatorRector::class,
        FinalizeClassesWithoutChildrenRector::class,
    ] );
};
