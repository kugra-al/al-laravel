<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $primaryKey = 'db_id';

    public static function getKeyCacheName() {
        return 'item_d-itm-keys';
    }

    public static function getValueCacheName() {
        return 'item_d-itm-values';
    }

    public static function castToIntCols() {
        $cols = [
            "earth",
            "wind",
            "water",
            "fire",
            "poison",
            "healing",
            "peace",
            "violence",
            "physical",
            "astral",
            "body",
            "spirit",
            "transmutation",
            "preservation",
            "warmth",
            "offensive",
            "weapon_quality",
            "obj_damage",
            "capacity",
            "clothing_size",
            "decay",
            "slow_decay",
            "lock",
            "uses",
            "tend_skill",
            "max_supported_weight",
            "slots",
            "ranged_type",
            "load_time",
            "ranged_weapon_quality",
            "set_slots",
            "leak",
            "quantity",
            "food_thirst",
            "furniture_area",
            "height",
            "light",
            "fuel_use",
            "paper_quality",
            "max_pages",
            "spell_level",
            "play_difficulty",
            "number_hands_needed",
            "hardness",
            "passengers",
            "plant_test",
            "regrowth_rate",
            "burn_amount",
            "light_level",
            "heat",
            "pilfer_difficulty",
            "heat_to_light",
            "lock_pick_value",
            "min_depth",
            "max_depth",
            "load_difficulty",
            "weight"
        ];
        return $cols;
    }

}
