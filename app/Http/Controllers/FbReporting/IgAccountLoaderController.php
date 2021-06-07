<?php

namespace App\Http\Controllers\FbReporting;

use App\Http\Controllers\Controller;
use App\Http\Requests\FbReporting\LoadIGAccountIdsRequest;
use App\Labs\StringManipulator;
use App\Revenuedriver\FacebookPage;
use App\Services\CampaignDuplicateService;
use App\Services\CampaignOptimizeTrackerService;
use App\Services\FbReporting\FbPagePostSchedulerService;
use Illuminate\Http\Request;

class IgAccountLoaderController extends Controller
{ 
    public function loadIDs(LoadIGAccountIdsRequest $request, StringManipulator $sm)
    {  
        $prep = $sm->generateArrayFromString(str_replace("\n", '<br />',  $request->fb_page_ids), '<br />');
        
        $data = [];
        if (count($prep) > 0) {
            $facebookPage = new FacebookPage; 
            foreach ($prep as $facebookPageId) {
                
                $igAccountId = $facebookPage->loadInstagramAccounts($facebookPageId, [
                    'id'
                ]);
             
                array_push($data, [
                    'facebook_page_id' => $facebookPageId,
                    'instagram_account_id' => $igAccountId[0] == true ? $igAccountId[1] : null
                ]);
            }
        }
        return $this->successResponse('Data returned successfully', $data);
    }
}
