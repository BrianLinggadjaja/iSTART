<?php

namespace App\Services;
use App\Models\ModuleProgress;
use App\Contracts\ModuleProgressContract;
use App\Jobs\SendNewModuleEmail;
use Illuminate\Support\Carbon;
use App\Jobs\SendReminderModuleEmail;
use Illuminate\Http\Request;


class ModuleProgressService implements ModuleProgressContract
{

    public function getModuleProgress($data): array
    {
        $response = [
            'user_id' => $data['user_id'],
            'current_module' => '',
            'current_page' => 0,
            'max_page' => '',
            'expiration_date' => null
        ];
        $moduleProgress = ModuleProgress::where('user_id',$data['user_id'])->orderBy('created_at', 'DESC')
                                            ->first();
        if ($moduleProgress !== null) {
            $response['user_id'] = $moduleProgress->user_id;
            $response['current_module'] = $moduleProgress->current_module;
            $response['current_page'] = $moduleProgress->current_page;
            $response['max_page'] = $moduleProgress->max_page;
            $response['expiration_date'] = $moduleProgress->expiration_date;
        }
        return $response;
    }

    public function setModuleProgress($data)
    {
        $moduleProgress = ModuleProgress::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'current_module' => $data['current_module']
            ],
            [
                'current_page' => $data['current_page'],
                'max_page' => $data['max_page']
            ]
        );
        return response()->json(true);
    }

    public function moduleComplete($data){
        $user = $data['user_id'];
        $moduleComplete = ModuleProgress::where('user_id', $data['user_id'])->where('current_module', $data['current_module'])->first();
        if($moduleComplete == null){

            return null;
        }
        $moduleComplete->expiration_date = null;
        $moduleComplete->touch();
        $moduleComplete->save();
        $newModule = $this->createNewModule($data);
        return $newModule;
        if ($newModule !== null) {
            return true;
        }
        return null;
    }

    public function createNewModule($data)
    {
        $moduleProgress = ModuleProgress::create([
            'user_id' => $data['user_id'],
            'current_module' => $data['next_module'],
            'current_page' => 0,
            'max_page' => 0,
            'expiration_date' => Carbon::now()->addDays(config('app.days_to_expire'))->toDateTimeString(),
        ]);
        return $moduleProgress;
    }

    public function moduleExists($data)
    {
        $moduleProgress = ModuleProgress::where('user_id', $data['user_id'])->whereNotNull('expiration_date')->orderBy('created_at', 'DESC')->first();
        if ($moduleProgress === null) {
            return false;
        }
        return true;
    }

}
