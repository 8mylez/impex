<?php

namespace Dustin\ImpEx\Sequence\Registry\Loader;

use Dustin\Exception\ConstraintViolationException;
use Dustin\ImpEx\Sequence\Registry\Config\SectionDefinition;
use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinitionContainer;
use Dustin\ImpEx\Sequence\Registry\Validation\SequenceClass;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
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
        $violationList = new ConstraintViolationList();
        $constraints = $this->getConstraints();

        foreach ($decoded as $id => $data) {
            $data = array_merge(
                (array) $data,
                ['id' => $id]
            );

            try {
                foreach ($this->createSequences($data, $constraints) as $sequence) {
                    $sequences->add($sequence);
                }
            } catch (ConstraintViolationException $exception) {
            }
        }
    }

    protected function createSequences(array $data, array|Collection $constraints): \Generator
    {
        $violations = $this->validator->validate($data, $constraints);

        if (count($violations) > 0) {
            throw new ConstraintViolationException($violations, $data);
        }

        foreach ($data as $sequence) {
        }
    }

    protected function getConstraints(): array|Collection
    {
        return new Collection([
            'allowExtraFields' => false,
            'fields' => [
                'id' => new NotBlank(),
                'class' => new NotBlank(), new SequenceClass(),
                'sections' => new All([
                    'type' => [new NotBlank(), new Choice([], [SectionDefinition::TYPE_SECTION, SectionDefinition::TYPE_SEQUENCE])],
                    'id' => new NotBlank(),
                    'priority' => new Type('integer'),
                ]),
            ],
        ]);
    }
}
