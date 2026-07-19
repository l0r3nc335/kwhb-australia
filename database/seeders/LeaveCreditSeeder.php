<?php

namespace Database\Seeders;

use App\Core\v1\Leave\Models\Leave;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class LeaveCreditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $annual = Leave::TYPE_ANNUAL;
        $personal = Leave::TYPE_PERSONAL;
        $compassionate = Leave::TYPE_COMPASSIONATE;
        $ceremonial = Leave::TYPE_CEREMONIAL;
        $specialSorry = Leave::TYPE_SPECIAL_OR_SORRY;
        $foil = Leave::TYPE_FOIL;
        $parental = Leave::TYPE_PARENTAL;
        $juryService = Leave::TYPE_JURY_SERVICE;
        $defenseReserve = Leave::TYPE_DEFENSE_RESERVE;
        $longService = Leave::TYPE_LONG_SERVICE;
        $incident = Leave::TYPE_INCIDENT_LEAVE;
        $study = Leave::TYPE_STUDY;
        $communityService = Leave::TYPE_COMMUNITY_SERVICE;

        Artisan::call("leave:add-credit pauluser --type={$annual} --credit=--credit=20");
        Artisan::call("leave:add-credit pauluser --type={$personal} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$compassionate} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$ceremonial} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$specialSorry} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$foil} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$parental} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$juryService} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$defenseReserve} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$longService} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$incident} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$study} --credit=20");
        Artisan::call("leave:add-credit pauluser --type={$communityService} --credit=20");

        Artisan::call("leave:add-credit paulmanager --type={$annual} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$personal} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$compassionate} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$ceremonial} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$specialSorry} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$foil} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$parental} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$juryService} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$defenseReserve} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$longService} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$incident} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$study} --credit=20");
        Artisan::call("leave:add-credit paulmanager --type={$communityService} --credit=20");

        Artisan::call("leave:add-credit beluser --type={$annual} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$personal} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$compassionate} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$ceremonial} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$specialSorry} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$foil} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$parental} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$juryService} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$defenseReserve} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$longService} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$incident} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$study} --credit=20");
        Artisan::call("leave:add-credit beluser --type={$communityService} --credit=20");

        Artisan::call("leave:add-credit belmanager --type={$annual} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$personal} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$compassionate} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$ceremonial} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$specialSorry} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$foil} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$parental} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$juryService} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$defenseReserve} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$longService} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$incident} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$study} --credit=20");
        Artisan::call("leave:add-credit belmanager --type={$communityService} --credit=20");

        Artisan::call("leave:add-credit uat_user --type={$annual} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$personal} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$compassionate} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$ceremonial} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$specialSorry} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$foil} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$parental} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$juryService} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$defenseReserve} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$longService} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$incident} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$study} --credit=20");
        Artisan::call("leave:add-credit uat_user --type={$communityService} --credit=20");

        Artisan::call("leave:add-credit uat_linemanager --type={$annual} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$personal} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$compassionate} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$ceremonial} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$specialSorry} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$foil} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$parental} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$juryService} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$defenseReserve} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$longService} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$incident} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$study} --credit=20");
        Artisan::call("leave:add-credit uat_linemanager --type={$communityService} --credit=20");

        Artisan::call("leave:add-credit uat_generalmanager --type={$annual} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$personal} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$compassionate} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$ceremonial} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$specialSorry} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$foil} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$parental} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$juryService} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$defenseReserve} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$longService} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$incident} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$study} --credit=20");
        Artisan::call("leave:add-credit uat_generalmanager --type={$communityService} --credit=20");
    }
}
