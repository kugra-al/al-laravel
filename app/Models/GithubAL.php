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

    public static function getLocalRepoPath($repo, $appendSlash = true) {
        $path = storage_path()."/private/git/".$repo;
        if ($appendSlash)
            $path .= "/";
        return $path;
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
        $data = preg_replace('/("room_coords":\(\[(.*?)\]\),)/','',$data);
        $data = preg_replace('/("persist_flags":\(\[(.*?)\]\),)/','',$data);
        $data = preg_replace('/("found":\(\[(.*?)\]\),)/','',$data);
        $data = str_replace('"who":,',"",$data);
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
            $converted = GithubAL::convertLPCDataToJson($t,$decode);
            if (!$converted)
                unset($tmp[$k]);
            else
                $tmp[$k] = $converted;
        }
        return $tmp;
    }

    public static function readVarsFromObjectData($data, $vars = ['touched_by', 'last_touched', 'psets'], $escape = false) {
        $out = [];

        foreach($vars as $var) {
            $regex = null;
            switch($var) {
                case "touched_by" :
                    $regex = '/"touched_by":\((.*?)\)/';
                    break;
                case 'last_touched' :
                    $regex = '/"last_touched":(\d+)/';
                    break;
                case 'psets' :
                    $regex = '/"psets":\((.*?)\)/';
                    break;
                case 'primary_id':
                    $regex = '/"primary_id":"(.*?)"/';
                    break;
                case 'primary_adj':
                    $regex = '/"primary_adj":"(.*?)"/';
                    break;
                case 'ids':
                    $regex = '/"ids":\(\[(.*?)\]\)/';
                    break;
                case 'adjs':
                    $regex = '/"adjs":\(\[(.*?)\]\)/';
                    break;
                case 'decay_value':
                    $regex = '/"decay_value":(\d+)/';
                    break;
                case 'last_decay_time':
                    $regex = '/"last_decay_time":(\d+)/';
                    break;
                case 'pathname':
                    $regex = '/"pathname":"(.*?)"/';
                    break;
                case 'short':
                    break;
            }
            $out[$var] = null;
            if ($escape) // For v1 object format
                $regex = str_replace('"','\\\\"',$regex);
            if ($regex) {
                preg_match($regex,$data,$matches);

                if (sizeof($matches) > 1) {
                    if ($matches[1] != "([])" && $matches[1] != "{}" && $matches[1] != '[]' && strlen($matches[1])) {
                        $out[$var] = $matches[1];
                    }
                }
            }
        }
        if (in_array("short",$vars)) {

            if (strlen($out["primary_id"]) || strlen($out["primary_adj"])) {
                $out['short'] = "";
                if (strlen($out["primary_adj"]))
                    $out["short"] .= $out["primary_adj"];
                if (strlen($out["primary_id"])) {
                    if (strlen($out["short"]))
                        $out["short"] .= " ";
                    $out["short"] .= $out["primary_id"];
                }
            }
                // dd($out);
        }
        return $out;
    }

    public static function getALRepos()
    {
        return [
            'Accursedlands-DATA',
            'Accursedlands-Domains',
            'Accursedlands-wiz',
            'Accursedlands-perms',
            'Accursedlands-obj',
            'Accursedlands-LOGS'
        ];
    }

    public static function getALRepoBranches()
    {
        return [
            'production_mud_fluffos',
            'master'
        ];
    }

    public static function getALTables()
    {
        return
            ['perms','perm_items','facades','deaths','items','perm_logs'];
    }

}

