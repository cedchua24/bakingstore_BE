<?php

namespace App\Services;

use App\Models\CheckList;
use App\Models\CheckListTransaction;
use Carbon\Carbon;

class CheckListTransactionService
{
    public function createStartOfDayTransactions($date)
    {
        $transactionDate = Carbon::parse($date);
        $checkLists = CheckList::query()
            ->from('check_list as cl')
            ->join('users as asg', 'asg.id', '=', 'cl.assignee')
            ->join('users as ch', 'ch.id', '=', 'cl.checker')
            ->select(
                'cl.id',
                'cl.check_list_name',
                'cl.assignee',
                'cl.checker',
                'cl.frequency',
                'cl.time_of_day'
            )
            ->where('asg.status', 0)
            ->where('ch.status', 0)
            ->orderByRaw("
                CASE cl.time_of_day
                    WHEN 'MORNING' THEN 1
                    WHEN 'AFTERNOON' THEN 2
                    WHEN 'EVENING' THEN 3
                    ELSE 4
                END
            ")
            ->get();

        foreach ($checkLists as $checkList) {
            $shouldCreate = false;

            if ($checkList->frequency == 'DAILY') {
                $shouldCreate = true;
            }

            if ($checkList->frequency == 'WEEKLY' && $transactionDate->isFriday()) {
                $shouldCreate = true;
            }

            if ($checkList->frequency == 'MONTHLY' && $transactionDate->isLastOfMonth()) {
                $shouldCreate = true;
            }

            if (!$shouldCreate) {
                continue;
            }
            $checkListTransaction = new CheckListTransaction;
            $checkListTransaction->check_list_id = $checkList->id;
            $checkListTransaction->assignee = $checkList->assignee;
            $checkListTransaction->checker = $checkList->checker;
            $checkListTransaction->grade = 0;
            $checkListTransaction->status = 'PENDING';
            $checkListTransaction->date = $date;
            $checkListTransaction->save();
        }

        return $checkLists->count();
    }
}
