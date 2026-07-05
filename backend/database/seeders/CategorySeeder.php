<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Gerçekçi Türkçe belediye kategorileri. Idempotent'tir (isim bazlı
 * firstOrCreate) ve production kurulumda da koşabilir.
 */
class CategorySeeder extends Seeder
{
    /**
     * @var list<array{name: string, icon: string, color: string, children?: list<array{name: string, icon: string, color: string}>}>
     */
    private const CATEGORIES = [
        ['name' => 'Yol ve Asfalt', 'icon' => 'road', 'color' => '#6B7280', 'children' => [
            ['name' => 'Çukur', 'icon' => 'alert-triangle', 'color' => '#B45309'],
            ['name' => 'Asfalt Bozulması', 'icon' => 'construction', 'color' => '#78716C'],
        ]],
        ['name' => 'Park ve Bahçeler', 'icon' => 'trees', 'color' => '#16A34A'],
        ['name' => 'Sokak Aydınlatması', 'icon' => 'lightbulb', 'color' => '#CA8A04'],
        ['name' => 'Çöp ve Temizlik', 'icon' => 'trash-2', 'color' => '#15803D'],
        ['name' => 'Su ve Kanalizasyon', 'icon' => 'droplet', 'color' => '#0EA5E9'],
        ['name' => 'Kaldırım ve Yaya Yolu', 'icon' => 'footprints', 'color' => '#9333EA'],
        ['name' => 'Trafik ve Sinyalizasyon', 'icon' => 'traffic-cone', 'color' => '#DC2626'],
        ['name' => 'Başıboş Hayvanlar', 'icon' => 'paw-print', 'color' => '#D97706'],
        ['name' => 'Diğer', 'icon' => 'more-horizontal', 'color' => '#64748B'],
    ];

    public function run(): void
    {
        $order = 0;

        foreach (self::CATEGORIES as $top) {
            $parent = Category::firstOrCreate(
                ['name' => $top['name']],
                ['icon' => $top['icon'], 'color' => $top['color'], 'is_active' => true, 'sort_order' => $order++],
            );

            $childOrder = 0;

            foreach ($top['children'] ?? [] as $child) {
                Category::firstOrCreate(
                    ['name' => $child['name']],
                    [
                        'parent_id' => $parent->id,
                        'icon' => $child['icon'],
                        'color' => $child['color'],
                        'is_active' => true,
                        'sort_order' => $childOrder++,
                    ],
                );
            }
        }
    }
}
