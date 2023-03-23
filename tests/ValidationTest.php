<?php

/**
 * @ Created on 14/03/2023 13:49
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Tests;

use App\Doctrine\Entity\User;
use PHPUnit\Framework\TestCase;
use App\DTO\EntityDecrypt\MediaDecrypt;
use App\DTO\EntityDecrypt\GroupDecrypt;
use Doctrine\ORM\EntityManagerInterface;
use App\DTO\EntityDecrypt\ArchiveDecrypt;
use Symfony\Component\Validator\Validation;
use App\Services\FileSystem\FileSystemInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ValidationTest.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
final class ValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $validatorBuilder = Validation::createValidatorBuilder();
        $this->validator = $validatorBuilder->enableAnnotationMapping()->getValidator();
    }

    public function testArchive(): void
    {
        $archive = new ArchiveDecrypt();

        // uuid //
        $violations = $this->validator->validateProperty($archive, "uuid");
        $this->assertSame(0, count($violations));

        $archive->uuid = "test";
        $violations = $this->validator->validateProperty($archive, "uuid");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $archive->uuid = "adfe6d6c-261a-4756-b391-6663a623535c";
        $violations = $this->validator->validateProperty($archive, "uuid");
        $this->assertSame(0, count($violations));


        // path //
        $violations = $this->validator->validateProperty($archive, "path");
        $this->assertSame(1, count($violations));
        $this->assertSame("c1051bb4-d103-4f74-8988-acbcafc7fdc3", $violations[0]->getCode());

        $archive->path = "test";
        $violations = $this->validator->validateProperty($archive, "path");
        $this->assertSame(0, count($violations));


        // metadata //
        $violations = $this->validator->validateProperty($archive, "metadata");
        $this->assertSame(0, count($violations));

        $archive->metadata = "test";
        $violations = $this->validator->validateProperty($archive, "metadata");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $archive->metadata = "{test";
        $violations = $this->validator->validateProperty($archive, "metadata");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $archive->metadata = "test}";
        $violations = $this->validator->validateProperty($archive, "metadata");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $archive->metadata = "{test}";
        $violations = $this->validator->validateProperty($archive, "metadata");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $archive->metadata = "[\"test\"]";
        $violations = $this->validator->validateProperty($archive, "metadata");
        $this->assertSame(0, count($violations));

        $archive->metadata = "{\"test\": \"ok\"}";
        $violations = $this->validator->validateProperty($archive, "metadata");
        $this->assertSame(0, count($violations));
    }

    public function testGroup(): void
    {
        $group = new GroupDecrypt();

        // uuid //
        $violations = $this->validator->validateProperty($group, "uuid");
        $this->assertSame(0, count($violations));

        $group->uuid = "test";
        $violations = $this->validator->validateProperty($group, "uuid");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $group->uuid = "adfe6d6c-261a-4756-b391-6663a623535c";
        $violations = $this->validator->validateProperty($group, "uuid");
        $this->assertSame(0, count($violations));


        // name ///
        $violations = $this->validator->validateProperty($group, "name");
        $this->assertSame(1, count($violations));
        $this->assertSame("c1051bb4-d103-4f74-8988-acbcafc7fdc3", $violations[0]->getCode());

        $group->name = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $violations = $this->validator->validateProperty($group, "name");
        $this->assertSame(1, count($violations));
        $this->assertSame("d94b19cc-114f-4f44-9cc4-4138e80a87b9", $violations[0]->getCode());

        $group->name = "test";
        $violations = $this->validator->validateProperty($group, "name");
        $this->assertSame(0, count($violations));
    }

    public function testMedia(): void
    {
        $media = new MediaDecrypt();

        // uuid //
        $violations = $this->validator->validateProperty($media, "uuid");
        $this->assertSame(0, count($violations));

        $media->uuid = "test";
        $violations = $this->validator->validateProperty($media, "uuid");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $media->uuid = "adfe6d6c-261a-4756-b391-6663a623535c";
        $violations = $this->validator->validateProperty($media, "uuid");
        $this->assertSame(0, count($violations));


        // name ///
        $violations = $this->validator->validateProperty($media, "name");
        $this->assertSame(1, count($violations));
        $this->assertSame("c1051bb4-d103-4f74-8988-acbcafc7fdc3", $violations[0]->getCode());

        $media->name = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $violations = $this->validator->validateProperty($media, "name");
        $this->assertSame(1, count($violations));
        $this->assertSame("d94b19cc-114f-4f44-9cc4-4138e80a87b9", $violations[0]->getCode());

        $media->name = "test";
        $violations = $this->validator->validateProperty($media, "name");
        $this->assertSame(0, count($violations));


        // type ///
        $violations = $this->validator->validateProperty($media, "type");
        $this->assertSame(1, count($violations));
        $this->assertSame("c1051bb4-d103-4f74-8988-acbcafc7fdc3", $violations[0]->getCode());

        $media->type = "aaaaaaaaa";
        $violations = $this->validator->validateProperty($media, "type");

        $this->assertSame(1, count($violations));
        $this->assertSame("d94b19cc-114f-4f44-9cc4-4138e80a87b9", $violations[0]->getCode());

        $media->type = "pdf";
        $violations = $this->validator->validateProperty($media, "type");
        $this->assertSame(0, count($violations));


        // metadata //
        $violations = $this->validator->validateProperty($media, "metadata");
        $this->assertSame(0, count($violations));

        $media->metadata = "test";
        $violations = $this->validator->validateProperty($media, "metadata");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $media->metadata = "{test";
        $violations = $this->validator->validateProperty($media, "metadata");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $media->metadata = "test}";
        $violations = $this->validator->validateProperty($media, "metadata");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $media->metadata = "{test}";
        $violations = $this->validator->validateProperty($media, "metadata");
        $this->assertSame(1, count($violations));
        $this->assertSame("f27e6d6c-261a-4056-b391-6673a623531c", $violations[0]->getCode());

        $media->metadata = "[\"test\"]";
        $violations = $this->validator->validateProperty($media, "metadata");
        $this->assertSame(0, count($violations));

        $media->metadata = "{\"test\": \"ok\"}";
        $violations = $this->validator->validateProperty($media, "metadata");
        $this->assertSame(0, count($violations));
    }

    public function testUser(): void
    {
        $user = new User();

        // email //
        $violations = $this->validator->validateProperty($user, "email");
        $this->assertSame(1, count($violations));
        $this->assertSame("c1051bb4-d103-4f74-8988-acbcafc7fdc3", $violations[0]->getCode());

        $user->setEmail("test");
        $violations = $this->validator->validateProperty($user, "email");
        $this->assertSame(1, count($violations));
        $this->assertSame("bd79c0ab-ddba-46cc-a703-a7a4b08de310", $violations[0]->getCode());

        $user->setEmail("test@test.testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest");
        $violations = $this->validator->validateProperty($user, "email");
        $this->assertSame(1, count($violations));
        $this->assertSame("d94b19cc-114f-4f44-9cc4-4138e80a87b9", $violations[0]->getCode());

        $user->setEmail("test@test.test");
        $violations = $this->validator->validateProperty($user, "email");
        $this->assertSame(0, count($violations));


        // password //
        $violations = $this->validator->validateProperty($user, "plainPassword");
        $this->assertSame(1, count($violations));
        $this->assertSame("c1051bb4-d103-4f74-8988-acbcafc7fdc3", $violations[0]->getCode());

        $user->setPlainPassword("tesT@_");
        $violations = $this->validator->validateProperty($user, "plainPassword");
        $this->assertSame(1, count($violations));
        $this->assertSame("9ff3fdc4-b214-49db-8718-39c315e33d45", $violations[0]->getCode());

        $user->setPlainPassword("testestest");
        $violations = $this->validator->validateProperty($user, "plainPassword");
        $this->assertSame(1, count($violations));
        $this->assertSame("de1e3db3-5ed4-4941-aae4-59f3667cc3a3", $violations[0]->getCode());

        $user->setPlainPassword("tesT@_86");
        $violations = $this->validator->validateProperty($user, "plainPassword");
        $this->assertSame(0, count($violations));
    }
}
