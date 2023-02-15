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
            if ($memberID == $member["id"])
                return true;
        }
        return false;
    }

    public static function getLocalRepoPath($repo) {
        return storage_path()."/private/git/".$repo."/";
    }

    public static function getLocalGitApiPath() {
        return storage_path()."/private/gitapi/";
    }

    public static function getDeathLogDir() {
        return GithubAL::getLocalGitApiPath()."deaths/";
    }

    public static function getCoordsFromLocation($location) {
        $coords = [];
        $location = str_replace(["/domains/player/built/data/"],"",$location);
        if (strpos($location,"/domains/wild/virtual/server/") !== false) {
            $tmp = str_replace("/domains/wild/virtual/server/","",$location);
            $tmp = explode("/",$tmp);

            $coords['x'] = (int)$tmp[0];
            $coords['y'] = (int)$tmp[1];
            $coords['z'] = (int)$tmp[2];
        }
        return $coords;
    }

    public static function convertLPCDataToJson($data, $decode = true) {
        $data = str_replace(['({', '})', '({|', '|})', '}', '{','([','])'], ['[', ']', '[', ']', '}', '{','{','}'], $data);
        $data = str_replace([",}",",]"],["}","]"],$data);
        $data = preg_replace('/:{0:(\d+)}/', ':[$1]', $data); // Edge case with arrays
        if ($decode)
            $data = json_decode($data, true);
        return $data;
    }

    public static function convertDataFromPermToJson($data,$decode = true) {
        $tmp = explode("\n",$data);
        $object = $tmp[0];
        unset($tmp[0]);
        foreach($tmp as $k=>$t) {
            if (str_ends_with($t,$object))
                $t = substr($t,0,(0-strlen($object)));
            $tmp[$k] = GithubAL::convertLPCDataToJson($t,$decode);
        }
        return $tmp;
    }
}

