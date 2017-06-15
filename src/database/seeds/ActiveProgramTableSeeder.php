<?php

use Bahjaat\Daisycon\Models\ActiveProgram;
use Illuminate\Database\Seeder;

class ActiveProgramTableSeeder extends Seeder
{

    public function run()
    {
        ActiveProgram::truncate();

        $programs = [
//	        10864 => array('custom_category' => 'Gogetaway'),
//	        10722 => array('custom_category' => 'Hotelkamerveiling'),
	        692 => array('custom_category' => 'Interhome NL'),
//	        3681 => array('custom_category' => 'Texel.nl'),
//	        683 => array('custom_category' => 'Tsjechoreizen'),
//            170 => array('custom_category' => 'zomer'),
//            191 => array('custom_category' => 'zomer'),
//            192 => array('custom_category' => ''),
//            387 => array('custom_category' => 'eindhoven'),
//            388 => array('custom_category' => 'maastricht'),
//            389 => array('custom_category' => 'rotterdam'),
//            390 => array('custom_category' => 'eelde'),
//            470 => array('custom_category' => ''),
//            694 => array('custom_category' => 'zomer'),
//            764 => array('custom_category' => 'zomer'),
//            864 => array('custom_category' => 'zomer'),
//            1571 => array('custom_category' => 'brussel'),
//            1572 => array('custom_category' => 'dusseldorf'),
//            2929 => array('custom_category' => 'schiphol'),
//            3663 => array('custom_category' => 'zomer')
//	        3681 => array('custom_category' => 'Texel.net'),
        ];

        foreach ($programs as $program => $attr) {
            ActiveProgram::create([
                'program_id' => $program,
                'status' => 1,
                'custom_category' => $attr['custom_category']
            ]);
        }

        $this->command->info('ActiveProgram table seeded!');
    }

}