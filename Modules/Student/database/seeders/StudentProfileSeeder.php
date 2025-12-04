<?php

namespace Modules\Student\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Demographic;
use App\Models\DemographicOption;

class StudentProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        Model::unguard();

        // Marital Status (Dropdown List)
        $marital = Demographic::firstOrCreate(
            ['question' => 'Marital Status'],
            [
                'type' => 'select', 
                'required' => true
            ]
        );

        $options = ['Single', 'Married', 'Common-law', 'Separated', 'Divorced', 'Widowed'];
        foreach ($options as $order => $opt) {
            DemographicOption::firstOrCreate([
                'demographic_id' => $marital->id,
                'label' => $opt
            ], ['order' => $order]);
        }

        // Profession (Text)
        Demographic::firstOrCreate(
            ['question' => 'Profession'],
            [
                'type' => 'text', 
                'required' => true
            ]
        );
    }
}
