<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\DificultatNormalizer;
use PHPUnit\Framework\TestCase;

class DificultatNormalizerTest extends TestCase
{
    public function test_normalitza_mitja_a_media(): void
    {
        $this->assertSame('media', DificultatNormalizer::normalitzar('mitja'));
        $this->assertSame('media', DificultatNormalizer::normalitzar('Mitjana'));
    }

    public function test_valors_bd_equivalent_inclou_aliases(): void
    {
        $variants = DificultatNormalizer::valorsBdEquivalent('media');

        $this->assertContains('media', $variants);
        $this->assertContains('mitja', $variants);
        $this->assertContains('mitjana', $variants);
    }
}
