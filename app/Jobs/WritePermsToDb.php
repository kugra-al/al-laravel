<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Models\GithubAL;
use App\Models\Perm;
use App\Models\PermItem;

class WritePermsToDb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
      // $this->handle();
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dir = GithubAL::getLocalRepoPath('Accursedlands-perms')."perm_objs/";
        $perms = [];
        $permItems = [];
        foreach(File::files($dir) as $file) {
            $filename = File::name($file);
            $contents = utf8_encode(File::get($file));
            $data = explode("\n",$contents);
            $perm = [
                'filename' => $filename,
                'object'   => null,
                'data'     => $contents,
                'x'        => null,
                'y'        => null,
                'z'        => null,
                'lastseen' => \Carbon\Carbon::now()->toDateTimeString(),
                'sign_title' => null,
                'touched_by' => null,
                'last_touched' => null,
                'psets' => null,

                'perm_id'=>null,
                'map_dir'=>null,
                'destroyed'=>null,
                'live'=>null,

                'save_type'=>null,
                'perm_type'=>"unknown",

                'is_inventory_container'=>false,
                'inventory_location'=>null,

                'primary_id'=>null,
                'primary_adj'=>null,
                'short'=>null,
                'pathname'=>null,
                'decay_value'=>null,
                'last_decay_time'=>null,

                'clean_filename'=>null,
            ];

            // Do any data reading stuff here on main save
            if (sizeof($data)) {
                $perm['object'] = $data[0];

                switch($perm['object']) {
                    case "/obj/base/misc/signpost":
                        preg_match('/"sign_title":"(.*?)","/',$perm['data'],$matches);
                        if (isset($matches[1]) && strlen($matches[1]))
                            $perm['sign_title'] = json_encode($matches[1]);
                        $perm["perm_type"] = "signpost";
                        break;
                    case "/std/shop_shelves":
                    case "/obj/base/misc/shop_shelves":
                        $perm["perm_type"] = "shop";
                        $perm["is_inventory_container"] = true;
                        $perm["save_type"] = "shop_stasis";
                        break;
                    case "/std/stasis_container":
                    case "/std/stasis/stasis_chest":
                        $perm["perm_type"] = "stasis_container";
                        $perm["is_inventory_container"] = true;
                        $perm["save_type"] = "stasis";
                        break;
                    case "/obj/items/other/large_canvas_tent":
                    case "/obj/items/other/conical_leather_tent":
                        $perm["perm_type"] = "tent";
                        $perm["is_inventory_container"] = true;
                        break;
                    case "/obj/base/misc/unfinished_perm":
                        $perm["perm_type"] = "unfinished";
                        $perm["save_type"] = "none";
                        break;
                    case "/domains/player_built/base/lock_installable_base_facade":
                    case "/domains/player_built/base/closeable_base_facade":
                    case "/obj/base/misc/permanent_building":
                        $perm["perm_type"] = "building";
                        $perm["is_inventory_container"] = true;
                        break;
                    case "/std/cart":
                    case "/obj/base/vehicles/rowboat":
                        $perm["perm_type"] = "vehicle";
                        $perm["save_type"] = "none";
                        break;
                    case "/obj/base/containers/permanent_well":
                        $perm["perm_type"] = "well";
                        $perm["save_type"] = "none";
                        break;
                    default :
                        $perm["perm_type"] = "unknown";
                        break;
                }
                $objectVars = GithubAL::readVarsFromObjectData($perm['data'],
                    ['touched_by','last_touched','psets','pathname','primary_id','primary_adj','decay_value','last_decay_time','short']
                );
                if ($objectVars['last_decay_time'])
                    $objectVars['last_decay_time'] = \Carbon\Carbon::createFromTimestamp($objectVars['last_decay_time'])->toDateTimeString();
                $perm = array_merge($perm,$objectVars);

            }

            $location = $filename;

            $playerBuilt = [];
            // Find map dirs for any /domains/player_built/ perms

            // These are all /obj/items/other/large_canvas_tent that have _domains_player_built in the filename
            if (strpos($filename,"_domains_player_built_data_") !== false && ($perm['perm_type'] == 'building' || $perm['perm_type'] == "tent")) {
                $location = str_replace("_domains_player_built_data_","",$location);
                preg_match("/(.*):(\d+)_city_server/",$location,$matches);
                $playerBuilt['perm_id'] = $matches[2];
                $playerBuilt['map_dir'] = $matches[1];
                $playerBuilt['save_type'] = 'stasis|perm';
            } else {
                // These are for all buildings
                if ($perm['perm_type'] == "building") {
                    $tmp = explode(":",$perm["filename"]);
                    $playerBuilt['perm_id'] = $tmp[1];
                    $playerBuilt['map_dir'] = $tmp[0];
                    $playerBuilt['save_type'] = 'stasis';
                }
                // These are for older tents
                if($perm['perm_type'] == "tent") {
                    $playerBuilt['save_type'] = 'perm';
                }
            }

            if (sizeof($playerBuilt)) {

                $perm["save_type"] = $playerBuilt["save_type"];
                if (isset($playerBuilt["map_dir"]) && isset($playerBuilt['perm_id']))
                    $perm["clean_filename"] = $playerBuilt["map_dir"].":".$playerBuilt['perm_id'];
                // Check for player_built saves
                //if ($playerBuilt['save_type'] == 'stasis') {
                if (isset($playerBuilt['perm_id']))
                    $perm['perm_id'] = $playerBuilt['perm_id'];
                // Items that save inventory
                if (strpos($contents,'"#inventory#":({"') !== false) {
                    $perm["save_type"] = "perm_broken";
                    $perm["inventory_location"] = "Accursedlands-perms/perm_objs/".$filename;
                } else {
                $check = false;
                    if (isset($playerBuilt['perm_id'])) {
                        $perm['perm_id'] = $playerBuilt['perm_id'];
                        $liveMapDir = "Accursedlands-Domains/player_built/data/".$playerBuilt['map_dir'].":".$perm['perm_id'];
                        if (File::exists(GithubAL::getLocalRepoPath($liveMapDir))) {
                            $perm['live'] = true;
                            $perm['destroyed'] = false;
                            $perm['map_dir'] = $liveMapDir;
                        }
                        $destroyedMapDir = "Accursedlands-Domains/player_built/destroyed/".$playerBuilt['map_dir'].":".$perm['perm_id'];
                        if (File::exists(GithubAL::getLocalRepoPath($destroyedMapDir))) {
                            $perm['destroyed'] = true;

                            if (File::exists(GithubAL::getLocalRepoPath($liveMapDir))) {
                                $perm['live'] = true;
                                $perm['map_dir'] = $liveMapDir.", ".$destroyedMapDir;
                            } else {
                                $perm['live'] = false;
                                $perm['map_dir'] = $destroyedMapDir;
                            }
                        }
                    }
                }
            }
            // Look for inventory
            if ($perm["is_inventory_container"] && !$perm["inventory_location"] && $perm['perm_type'] != 'shop' && $perm['perm_type'] != 'stasis_container') {
                // Find file from perm filename
                //  _domains_autra_city_server_10_0_0:41544
                // _domains_autra_city_server_10_0_0:_obj_items_other_large_canvas_tent:_perms_perm_objs__domains_autra_city_server_10_0_0:41544
                $search = "Accursedlands-DATA/permanent_rooms/";
                $tmp = explode(":",$filename);
                $search .= $tmp[0].":".str_replace("/","_",$perm['object']).":_perms_perm_objs_".$filename;
                $fileSearch = substr(GithubAL::getLocalRepoPath($search),0,-1);

                if (File::exists($fileSearch)) {
                    $perm["inventory_location"] = $search;
                    $perm["save_type"] = "data";
                }
                else {
               // if ($filename == "_domains_player_built_data__domains_wild_virtual_server_467_1373_0:60311_city_server_0_0_0:64796")
               // dd('ok');
                    // Find file like
                    // _domains_player_built_data__domains_wild_virtual_server_467_1373_0:60311_city_server_0_0_0:64796
                    $search = "Accursedlands-DATA/permanent_rooms/";
                    $tmp = explode(":",$filename);
                    if (sizeof($tmp) === 3)
                        $tmp = $tmp[0].":".$tmp[1];
                    else
                        $tmp = $filename;

                    $search .= ":".$tmp.":";

                    $fileSearch = substr(GithubAL::getLocalRepoPath($search),0,-1);
                    if (File::exists($fileSearch)) {
                        $perm["inventory_location"] = $search;
                        $perm["save_type"] = "data";
                    } else {
                        // Find
                        // _domains_wild_virtual_server_612_1433_0:89777
                        $search = "Accursedlands-DATA/permanent_rooms/";
                        $tmp = "_domains_player_built_data_".$filename."_city_server_0_0_0";
                        $search .= ":".$tmp.":";

                        $fileSearch = substr(GithubAL::getLocalRepoPath($search),0,-1);
                        if (File::exists($fileSearch)) {
                            $perm["inventory_location"] = $search;
                            $perm["save_type"] = "data";
                        } else {
                            $perm["save_type"] = "unknown";
                        }

                    }
                }
            }

            $location = str_replace("_","/",$location);
            $location = explode(":",$location)[0];
            $location = str_replace('city/server','city_server',$location);
           // $location = str_replace("/domains/player/built/data/","",$location);
            $perm['location'] = $location;
            $coords = GithubAL::getCoordsFromLocation($location);
            if (sizeof($coords) && sizeof($coords) == 3)
                $perm = array_merge($perm,$coords);


            $perms[] = $perm;
        }

        if (sizeof($perms)) {
            Perm::upsert(
                $perms,
                ['filename'],['data','lastseen','x','y','z','object','location','map_dir','destroyed','live','perm_type','save_type','is_inventory_container','inventory_location','touched_by','last_touched','psets','pathname','primary_id','primary_adj','decay_value','last_decay_time','short','clean_filename']
            );
            \Log::info("Wrote perms");
        } else {
            \Log::info("Nothing to write");
        }

    }

}
