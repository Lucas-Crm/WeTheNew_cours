<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Traits\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserEntityTest extends KernelTestCase
{
    use TestTrait;

    protected ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

    }

    public function testRepositoryCount(): void
    {
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/UserFixtures.yaml',
        ]);

        $userRepo = self::getContainer()->get(UserRepository::class);

        $users = $userRepo->findAll();

        self::assertCount(12, $users);

    }

    public function getEntity(): User
    {
        return (new User)
            ->setEmail('test@test.com')
            ->setFirstName('test')
            ->setLastName('lastname')
            ->setPassword('pwd');
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());

    }

    /**
     * @dataProvider provideEmail
     *
     * @param string $email
     * @param int $number
     * @return void
     */
    public function testInvalidEmail(string $email, int $number): void
    {
        $user = $this->getEntity()
            ->setEmail($email);

        $this->assertHasErrors($user, $number);
    }

    /**
     * @dataProvider provideName
     *
     * @param string $name
     * @param int $number
     * @return void
     */
    public function testInvalidFirstName(string $name, int $number): void
    {
        $user = $this->getEntity()
            ->setFirstName($name);

        $this->assertHasErrors($user, $number);
    }

    /**
     * @dataProvider provideName
     * @param string $name
     * @param int $number
     * @return void
     */
    public function testLastName(string $name, int $number)
    {

        $user = $this->getEntity()
            ->setLastName($name);

        $this->assertHasErrors($user, 1);

    }

    /**
     *
     * @dataProvider providePhone
     * @param string $phoneNumber
     * @param int $number
     * @return void
     */
    public function testPhone(string $phoneNumber, int $number)
    {
        $user = $this->getEntity()
            ->setPhone($phoneNumber);

        $this->assertHasErrors($user, $number);
    }

    public function testFindPaginateOrderByDate(): void
    {

        $repos = self::getContainer()->get(UserRepository::class);

        $users = $repos->findPaginateOrderByDate(9, 1);

        $this->assertCount(9, $users);
    }

    public function testFindPaginateOrderByDateWithSearch(): void
    {

        $repos = self::getContainer()->get(UserRepository::class);

        $users = $repos->findPaginateOrderByDate(9, 1, 'admin');

        $this->assertCount(1, $users);

    }

    public function testFindPaginateOrderByDateWithInvalidArgument()
    {
        $repos = self::getContainer()->get(UserRepository::class);

        $this->expectException(\TypeError::class);

        $users = $repos->findPaginateOrderByDate('test', 1, 'invalid');

    }

    public function providePhone(): array
    {
        return [
            'max_length' => [
                'phoneNumber' => str_repeat('1', 11),
                'number' => 1
            ],
            'min_length' => [
                'phoneNumber' => str_repeat('1', 9),
                'number' => 1
            ]
        ];
    }

    public function provideName(): array
    {
        return [
            'max_length' => [
                'name' => str_repeat('a', 256),
                'number' => 1
            ],
            'not_blank' => [
                'name' => '',
                'number' => 1
            ]
        ];
    }

    public function provideEmail(): array
    {
        return[
            'non_unique' => [
                'email' => 'admin@test.com',
                'number' => 1
            ],
            'max_length' => [
                'email' => str_repeat('a', 180) . '@test.com',
                'number' => 1
            ],
            'not_blank' => [
                'email' => '',
                'number' => 1
            ],
            'not_email' => [
                'email' => 'admin',
                'number' => 1
            ],
        ];
    }

    public function tearDown(): void
    {

        $this->databaseTool = null;

        parent::tearDown();

    }

}