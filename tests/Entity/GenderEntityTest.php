<?php

namespace App\Tests\Entity;

use App\Entity\Product\Gender;
use App\Repository\Product\GenderRepository;
use App\Tests\Traits\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenderEntityTest extends KernelTestCase
{
    use TestTrait;

    protected ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

    }

    public function testRepositoryGenderCount(): void
    {
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/GenderFixtures.yaml',
        ]);

        $genderRepo = self::getContainer()->get(GenderRepository::class);

        $genders = $genderRepo->findAll();

        $this->assertCount(3, $genders);
    }
    public function getEntity(): Gender
    {
        return (new Gender)
            ->setName('test')
            ->setEnable(true);
    }

    public function testValidGenderEntity(): void
    {
        $this->assertHasErrors( $this->getEntity());
    }

    /**
     * @dataProvider provideName
     * @param string $name
     * @param int $number
     * @return void
     */
    public function testInvalidGenderName(string $name, int $number)
    {
        $gender = $this->getEntity()
            ->setName($name);

        $this->assertHasErrors($gender, $number);
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
            ],
        ];
    }


    public function tearDown(): void
    {
        $this->databaseTool = null;

        parent::tearDown();
    }

}