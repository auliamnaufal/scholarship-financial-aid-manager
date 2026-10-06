<?php

namespace Tests\Unit;

use App\Enums\ProgramType;
use App\Models\Program;
use Database\Factories\ProgramFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ProgramCoverTest extends TestCase
{
    /** @return list<string> every scholarship name the seed data can produce */
    private function seededNames(): array
    {
        $constants = (new ReflectionClass(ProgramFactory::class))->getConstants();

        return array_merge($constants['NEED_NAMES'], $constants['MERIT_NAMES']);
    }

    public function test_every_seeded_scholarship_has_a_cover_chosen_for_it(): void
    {
        $covers = (new ReflectionClass(Program::class))->getConstant('COVERS');

        foreach ($this->seededNames() as $name) {
            $this->assertArrayHasKey($name, $covers, "{$name} has no cover photo");
        }
    }

    public function test_no_two_seeded_scholarships_share_a_cover(): void
    {
        $urls = array_map(
            fn (string $name) => (new Program(['name' => $name]))->coverImage(),
            $this->seededNames(),
        );

        $this->assertSame(count($urls), count(array_unique($urls)));
    }

    public function test_a_programme_with_another_name_still_gets_a_stable_cover(): void
    {
        $program = new Program(['name' => 'Beasiswa Percobaan', 'type' => ProgramType::MeritBased]);

        $this->assertSame($program->coverImage(), $program->coverImage());
        $this->assertStringStartsWith('https://images.unsplash.com/photo-', $program->coverImage());
    }
}
