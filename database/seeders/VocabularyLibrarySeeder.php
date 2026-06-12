<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VocabularyLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $cvc = [
            ['Bag', 'Bag'], ['Bed', 'Kama'], ['Bus', 'Bus'], ['Can', 'Lata'], ['Cap', 'Gora'],
            ['Cat', 'Pusa'], ['Cup', 'Tasa'], ['Dog', 'Aso'], ['Egg', 'Itlog'], ['Fan', 'Pamaypay'],
            ['Hat', 'Sombrero'], ['Hen', 'Manok'], ['Jar', 'Garapon'], ['Jug', 'Pitsel'], ['Key', 'Susi'],
            ['Kit', 'Kahon'], ['Log', 'Troso'], ['Map', 'Mapa'], ['Mat', 'Banig'], ['Mop', 'Mop'],
            ['Net', 'Net'], ['Pan', 'Kawali'], ['Pen', 'Panulat'], ['Pig', 'Baboy'], ['Pin', 'Karayom'],
            ['Pot', 'Palayok'], ['Rat', 'Daga'], ['Rug', 'Alpombra'], ['Tap', 'Gripo'], ['Tin', 'Lata'],
            ['Top', 'Trumpo'], ['Tub', 'Banyera'], ['Van', 'Van'], ['Web', 'Bahay-Gagamba'], ['Box', 'Kahon'],
            ['Fox', 'Lobo'], ['Axe', 'Palakol'], ['Bun', 'Tinapay'], ['Bug', 'Kulisap'], ['Cub', 'Anak ng Oso'],
            ['Gun', 'Baril'], ['Hut', 'Kubo'], ['Pup', 'Tuta'], ['Rod', 'Baras'], ['Sub', 'Submarino'],
            ['Cot', 'Papag'], ['Dot', 'Tuldok'], ['Hot', 'Mainit'], ['Lot', 'Lote'], ['Nut', 'Mani'],
        ];

        $multi = [
            ['Apple', 'Mansanas'], ['Basket', 'Basket'], ['Bottle', 'Bote'], ['Candle', 'Kandila'],
            ['Chair', 'Silya'], ['Clock', 'Orasan'], ['Crayon', 'Krayola'], ['Eraser', 'Pambura'],
            ['Flower', 'Bulaklak'], ['Glasses', 'Salamin'], ['Hammer', 'Martilyo'], ['Jacket', 'Dyaket'],
            ['Kettle', 'Takure'], ['Ladder', 'Hagdan'], ['Mango', 'Mangga'], ['Mirror', 'Salamin'],
            ['Monkey', 'Unggoy'], ['Napkin', 'Serbilyeta'], ['Onion', 'Sibuyas'], ['Orange', 'Dalandan'],
            ['Pencil', 'Lapis'], ['Pillow', 'Unan'], ['Rabbit', 'Kuneho'], ['Rubber', 'Goma'],
            ['Sandal', 'Sandalyas'], ['Scissors', 'Gunting'], ['Slippers', 'Tsinelas'], ['Spoon', 'Kutsara'],
            ['Tablet', 'Tablet'], ['Toothbrush', 'Sipilyo'], ['Towel', 'Tuwalya'], ['Umbrella', 'Payong'],
            ['Wallet', 'Pitaka'], ['Window', 'Bintana'], ['Balloon', 'Lobo'], ['Banana', 'Saging'],
            ['Biscuit', 'Biskwit'], ['Blanket', 'Kumot'], ['Bookshelf', 'Istante'], ['Butterfly', 'Mariposa'],
            ['Cabinet', 'Aparador'], ['Canteen', 'Bote ng Tubig'], ['Carrot', 'Karot'], ['Curtain', 'Kurtina'],
            ['Dustpan', 'Pangwalis'], ['Feather', 'Balahibo'], ['Lantern', 'Parol'], ['Notebook', 'Kuwaderno'],
            ['Pineapple', 'Pinya'], ['Toothpaste', 'Toothpaste'],
        ];

        $rows = [];

        foreach ($cvc as [$english, $filipino]) {
            $rows[] = [
                'noun_anchor'        => strtolower($english),
                'category'           => 'CVC',
                'filipino_label'     => $filipino,
                'english_label'      => $english,
                'filipino_audio_url' => null,
                'english_audio_url'  => null,
                'audio_status'       => 'Missing',
                'complexity_level'   => 1,
                'is_active'          => true,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        foreach ($multi as [$english, $filipino]) {
            $rows[] = [
                'noun_anchor'        => strtolower($english),
                'category'           => 'Multi-Syllabic',
                'filipino_label'     => $filipino,
                'english_label'      => $english,
                'filipino_audio_url' => null,
                'english_audio_url'  => null,
                'audio_status'       => 'Missing',
                'complexity_level'   => 2,
                'is_active'          => true,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        DB::table('vocabulary_library')->insertOrIgnore($rows);
    }
}
