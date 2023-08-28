<?php

namespace Dustin\ImpEx\Sequence\Registry\Validation;

use Dustin\ImpEx\Sequence\Registry\Config\SectionDefinition;
use Dustin\ImpEx\Sequence\Registry\Loader\SequenceDefinitionDetector;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SectionValidator
{
    public static function validate(array $section, ExecutionContextInterface $context, $payload = null): void
    {
        $fields = [
            'type' => [new NotBlank(), new Choice([], [SectionDefinition::TYPE_SECTION, SectionDefinition::TYPE_SEQUENCE])],
            'id' => [new NotBlank()],
            'priority' => [new Type('integer')],
        ];

        $type = $section['type'] ?? null;

        if ($type === SectionDefinition::TYPE_SEQUENCE && SequenceDefinitionDetector::isSequenceDefinition($section)) {
            $fields = array_merge($fields, [
                'class' => [new NotBlank(), new SequenceClass()],
                'sections' => new All([new Callback([self::class, 'validate'])]),
            ]);
        }

        $constraints = new Collection([
            'allowExtraFields' => false,
            'fields' => $fields,
        ]);

        $violations = $context->getValidator()
            ->startContext()
            ->atPath($context->getPropertyPath())
            ->validate($section, $constraints)
            ->getViolations();

        foreach ($violations as $violation) {
            $context->getViolations()->add($violation);
        }
    }
}
