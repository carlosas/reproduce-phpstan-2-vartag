<?php

declare(strict_types=1);

namespace Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;
use PhpParser\Node\Expr\Variable;

class NoVarAnnotationRule implements Rule
{
    public function __construct(
        private FileTypeMapper $fileTypeMapper
    ) {}

    public function getNodeType(): string
    {
        return Variable::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Variable) {
            return [];
        }

        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        $traitReflection = $scope->getTraitReflection();
        $functionReflection = $scope->getFunction();

        $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $classReflection->getName(),
            $traitReflection ? $traitReflection->getName() : null,
            $functionReflection ? $functionReflection->getName() : null,
            $docComment->getText()
        );

        $errors = [];
        foreach ($resolvedPhpDoc->getVarTags() as $tag) {
            foreach ($tag->getType()->getReferencedClasses() as $class) {
                $errors[] = RuleErrorBuilder::message("forbidden use of @var annotation with class {$class}")
                    ->identifier("forbiddenUseOfVarAnnotation")
                    ->build();
            }
        }

        return $errors;
    }
}
