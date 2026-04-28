<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class QuestionnaireMappingTest extends TestCase
{
    public function test_categoria_mapping_from_goal(): void
    {
        $mapping = [
            'Salut' => 1,
            'Productivitat' => 2,
            'Ment' => 3,
            'Aprenentatge' => 4,
        ];

        $this->assertEquals(1, $mapping['Salut']);
        $this->assertEquals(2, $mapping['Productivitat']);
        $this->assertEquals(3, $mapping['Ment']);
        $this->assertEquals(4, $mapping['Aprenentatge']);
    }

    public function test_senal_mapping_from_energy(): void
    {
        $mapping = [
            'Matí' => 'matí',
            'Migdia' => 'migdia',
            'Tarda' => 'tarda',
            'Nit' => 'nit',
        ];

        $this->assertEquals('matí', $mapping['Matí']);
        $this->assertEquals('migdia', $mapping['Migdia']);
        $this->assertEquals('tarda', $mapping['Tarda']);
        $this->assertEquals('nit', $mapping['Nit']);
    }

    public function test_dificultat_mapping_from_obstacle(): void
    {
        $mapping = [
            'Estrès' => 'facil',
            'Temps' => 'mitjana',
            'Memòria' => 'mitjana',
            'Mandra' => 'dificil',
        ];

        $this->assertEquals('facil', $mapping['Estrès']);
        $this->assertEquals('mitjana', $mapping['Temps']);
        $this->assertEquals('mitjana', $mapping['Memòria']);
        $this->assertEquals('dificil', $mapping['Mandra']);
    }

    public function test_objectiu_vegades_mapping_from_time(): void
    {
        $mapping = [
            '<15m' => 1,
            '30m' => 1,
            '1h' => 1,
            '+1h' => 2,
        ];

        $this->assertEquals(1, $mapping['<15m']);
        $this->assertEquals(1, $mapping['30m']);
        $this->assertEquals(1, $mapping['1h']);
        $this->assertEquals(2, $mapping['+1h']);
    }
}
