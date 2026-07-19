<?php

namespace App\Core\v1\Timesheet\Models;

use App\Core\v1\Leave\Models\Leave;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory;

    protected $appends = ['categories'];

    const CATEGORY_ANNUAL_LEAVE = 1;
    const CATEGORY_CEREMONIAL_LEAVE = 2;
    const CATEGORY_COMMUNITY_SERVICE_LEAVE = 3;
    const CATEGORY_COMPASSIONATE_LEAVE = 4;
    const CATEGORY_DEFENSE_RESERVE_LEAVE = 5;
    const CATEGORY_FLEX_TIME = 6;
    const CATEGORY_FOIL = 7;
    const CATEGORY_JURY_SERVICE_LEAVE = 8;
    const CATEGORY_LEAVE_WITHOUT_PAY = 9;
    const CATEGORY_LONG_SERVICE_LEAVE = 10;
    const CATEGORY_ON_CALL = 11;
    const CATEGORY_CALL_OUT_PHONE_CALL = 12;
    const CATEGORY_CALL_OUT_ON_SITE = 13;
    const CATEGORY_ORDINARY_HOURS = 14;
    const CATEGORY_OVERTIME_PAID = 15;
    const CATEGORY_OVERTIME_TOIL = 16;
    const CATEGORY_PARENTAL_LEAVE = 17;
        // Sexual, Family, Domestic Violence
    const CATEGORY_INCIDENT_LEAVE = 19;
    const CATEGORY_SPECIAL_LEAVE = 20;
    const CATEGORY_SORRY_LEAVE = 21;
    const CATEGORY_STUDY_LEAVE = 22;
    const CATEGORY_TOIL_LEAVE = 23;
    const CATEGORY_PERSONAL_LEAVE = 24;

    public static function categories(): array
    {
        return [
            self::CATEGORY_FLEX_TIME => 'Flex Time',
            self::CATEGORY_FOIL => 'FOIL',
            self::CATEGORY_CALL_OUT_PHONE_CALL => 'Call Out (Phone Call)',
            self::CATEGORY_CALL_OUT_ON_SITE => 'Call Out (On Site)',
            self::CATEGORY_ORDINARY_HOURS => 'Ordinary Hours',
            self::CATEGORY_ON_CALL => 'On Call',
            self::CATEGORY_OVERTIME_PAID => 'Overtime (Paid)',
            self::CATEGORY_OVERTIME_TOIL => 'Overtime (TOIL)',
        ];
    }

    public static function leaveCategories(): array
    {
        return [
            self::CATEGORY_ANNUAL_LEAVE => 'Annual Leave',
            self::CATEGORY_CEREMONIAL_LEAVE => 'Ceremonial Leave',
            self::CATEGORY_COMMUNITY_SERVICE_LEAVE => 'Community Service Leave',
            self::CATEGORY_COMPASSIONATE_LEAVE => 'Compassionate Leave',
            self::CATEGORY_DEFENSE_RESERVE_LEAVE => 'Defense Reserve Leave',
            self::CATEGORY_JURY_SERVICE_LEAVE => 'Jury Service Leave',
            self::CATEGORY_LEAVE_WITHOUT_PAY => 'Leave Without Pay',
            self::CATEGORY_LONG_SERVICE_LEAVE => 'Long Service Leave',
            self::CATEGORY_PARENTAL_LEAVE => 'Parental Leave',
            self::CATEGORY_INCIDENT_LEAVE => 'Incident Leave (Sexual, Family, Domestic Violence)',
            self::CATEGORY_SPECIAL_LEAVE => 'Special Leave',
            self::CATEGORY_SORRY_LEAVE => 'Sorry Leave',
            self::CATEGORY_STUDY_LEAVE => 'Study Leave',
            self::CATEGORY_TOIL_LEAVE => 'TOIL Leave',
            self::CATEGORY_PERSONAL_LEAVE => 'Personal Leave'
        ];
    }

    public static function mapLeaveTimesheetCategories(): array
    {
        return [
            Leave::TYPE_ANNUAL => self::CATEGORY_ANNUAL_LEAVE,
            Leave::TYPE_CEREMONIAL => self::CATEGORY_CEREMONIAL_LEAVE,
            Leave::TYPE_COMMUNITY_SERVICE => self::CATEGORY_COMMUNITY_SERVICE_LEAVE,
            Leave::TYPE_COMPASSIONATE => self::CATEGORY_COMPASSIONATE_LEAVE,
            Leave::TYPE_DEFENSE_RESERVE => self::CATEGORY_DEFENSE_RESERVE_LEAVE,
            Leave::TYPE_JURY_SERVICE => self::CATEGORY_JURY_SERVICE_LEAVE,
            Leave::TYPE_LEAVE_WITHOUT_PAY => self::CATEGORY_LEAVE_WITHOUT_PAY,
            Leave::TYPE_LONG_SERVICE => self::CATEGORY_LONG_SERVICE_LEAVE,
//            Leave::ON_CALL => self::CATEGORY_ON_CALL,
            Leave::TYPE_PARENTAL => self::CATEGORY_PARENTAL_LEAVE,
            Leave::TYPE_INCIDENT_LEAVE => self::CATEGORY_INCIDENT_LEAVE,
            Leave::TYPE_SPECIAL_OR_SORRY => [
                self::CATEGORY_SPECIAL_LEAVE,
                self::CATEGORY_SORRY_LEAVE
            ],
            Leave::TYPE_STUDY => self::CATEGORY_STUDY_LEAVE,
            Leave::TYPE_FOIL => self::CATEGORY_TOIL_LEAVE
        ];
    }

    public function getCategoriesAttribute(): array
    {
        $categories = self::categories();

        $leaves = Leave::query()->where('user_id', $this->user_id)->where('date', $this->date)->where('status', Leave::STATUS_APPROVED)->get();
        $activeLeaveCategories = [];

        foreach ($leaves as $leave) {
            $leaveCategory = self::mapLeaveTimesheetCategories()[$leave->type] ?? null;
            if (!empty($leaveCategory)) {
                if (is_array($leaveCategory)) {
                    foreach ($leaveCategory as $lc) {
                        $activeLeaveCategories[] = $lc;
                    }
                } else {
                    $activeLeaveCategories[] = $leaveCategory;
                }
            }
        }


        $activeLeaveCategories = array_unique($activeLeaveCategories);

        foreach ($activeLeaveCategories as $activeLeaveCategory) {
            $categories[$activeLeaveCategory] = self::leaveCategories()[$activeLeaveCategory];
        }

        $objectCategories = [];

        foreach ($categories as $id => $name) {
            $c = new \stdClass();
            $c->id = $id;
            $c->name = $name;
            $objectCategories[] = $c;
        }

        return $objectCategories;
    }
}
