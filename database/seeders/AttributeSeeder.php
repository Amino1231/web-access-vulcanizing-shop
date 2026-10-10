<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Size'      => ['175/65 R14', '185/65 R15', '195/55 R16', '205/55 R16'],
            'Load Index'=> ['82T', '88H', '91V', '94W'],
            'Season'    => ['Summer', 'All-Season', 'Winter'],
        ];

        foreach (Tenant::all() as $tenant) {
            foreach ($attributes as $name => $values) {
                $attr = Attribute::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $name],
                    []
                );

                foreach ($values as $value) {
                    AttributeValue::updateOrCreate(
                        ['attribute_id' => $attr->id, 'value' => $value],
                        []
                    );
                }
            }
        }
    }
}