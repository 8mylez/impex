<?php

namespace Dustin\ImpEx\Sequence\Registry\Loader;

use Dustin\Exception\ConstraintViolationException;
use Dustin\ImpEx\Sequence\Registry\Config\SectionDefinition;
use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinition;
use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinitionContainer;
use Dustin\ImpEx\Sequence\Registry\Validation\SectionValidator;
use Dustin\ImpEx\Sequence\Registry\Validation\SequenceClass;
use Dustin\ImpEx\Util\ArrayUtil;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileSequenceLoader implements SequenceLoaderInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        private string $file,
        private string $format,
        private ValidatorInterface $validator,
        ?EncoderInterface $encoder = null
    ) {
        $encoders = [new JsonEncoder(null, new JsonDecode([JsonDecode::ASSOCIATIVE => true])), new YamlEncoder()];

        if ($encoder !== null) {
            $encoders[] = $encoder;
        }

        $this->serializer = new Serializer([], $encoders);
    }

    public function load(): SequenceDefinitionContainer
    {
        if (!is_file($this->file)) {
            throw new FileNotFoundException(null, 0, null, $this->file);
        }

        $decoded = $this->serializer->decode(file_get_contents($this->file), $this->format);
        $sequences = new SequenceDefinitionContainer();
        $violations = new ConstraintViolationList();
        $constraints = $this->getConstraints();
        $sequenceIds = [];

        foreach ($decoded as $id => $data) {
            $data = array_merge(
                ArrayUtil::ensure($data),
                ['id' => $id]
            );

            $sequenceViolations = $this->validator->startContext()
                ->atPath($id)
                ->validate($data, $constraints)
                ->getViolations();

            if (count($sequenceViolations) > 0) {
                foreach ($sequenceViolations as $violation) {
                    $violations->add($violation);
                }

                continue;
            }

            try {
                foreach ($this->createSequences($data, $sequenceIds, $id) as $sequence) {
                    $sequences->add($sequence);
                }
            } catch (ConstraintViolationException $e) {
                foreach ($e->getViolations() as $violation) {
                    $violations->add($violation);
                }
            }
        }

        if (count($violations) > 0) {
            throw new ConstraintViolationException($violations, $decoded);
        }

        return $sequences;
    }

    protected function getConstraints(): array|Collection
    {
        return new Collection([
            'allowExtraFields' => false,
            'fields' => [
                'id' => new NotBlank(),
                'class' => [new NotBlank(), new SequenceClass()],
                'sections' => new All([
                    new Callback([SectionValidator::class, 'validate']),
                ]),
            ],
        ]);
    }

    private function createSequences(array $data, array &$usedIds, string $path): \Generator
    {
        $violations = new ConstraintViolationList();
        $id = $data['id'];

        if (isset($usedIds[$id])) {
            $violations->add(new ConstraintViolation(
                'Duplicate sequence ids.',
                '',
                ['id' => $id],
                $data,
                $path.'[id]',
                $id
            ));
        }

        $usedIds[$id] = $id;

        $definition = new SequenceDefinition([
            'id' => $data['id'],
            'class' => $data['class'],
        ]);

        foreach ($data['sections'] as $index => $sectionData) {
            $definition->getSections()->add(new SectionDefinition([
                'id' => $sectionData['id'],
                'type' => $sectionData['type'],
                'priority' => (int) $sectionData['priority'],
            ]));

            if ($sectionData['type'] === SectionDefinition::TYPE_SEQUENCE && SequenceDefinitionDetector::isSequenceDefinition($sectionData)) {
                try {
                    yield from $this->createSequences($sectionData, $usedIds, $path.'[sections]['.$index.']');
                } catch (ConstraintViolationException $e) {
                    foreach ($e->getViolations() as $violation) {
                        $violations->add($violation);
                    }
                }
            }
        }

        if (count($violations) > 0) {
            throw new ConstraintViolationException($violations, $data);
        }

        yield $definition;
    }
}
