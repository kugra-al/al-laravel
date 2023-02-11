<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use GitHub;

class GithubAL extends Model
{
    use HasFactory;

    public static function checkMemberIDInTeam($organization, $team, $memberID)
    {
        $cacheKey = "github_team_members_{$organization}_{$team}";
        $members = Cache::get($cacheKey);
        if (!$members) {
            $members = Github::api('organizations')->teams()->members($team,$organization);
            Cache::put($cacheKey, $members, now()->addHours(24));
        }
        foreach($members as $member) {
            if ($memberID == $member["id"];
                return true;
        }
        return false;
    }
}
