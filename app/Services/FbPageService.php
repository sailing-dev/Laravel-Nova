<?php

namespace App\Services;

use App\Models\FbReporting\FbPage;
use App\Models\FbReporting\Rpc;
use Carbon\Carbon;

class FbPageService
{ 

    /**
     * @param array $data
     * 
     * @return bool|null
     */
    public function create(string $pageName, string $pageId, $instagramId, $environment): ?bool
    { 
        FbPage::create([
            'page_name' => $pageName,
            'page_id' => $pageId,
            'instagram_id' => $instagramId,
            'environment' => $environment
        ]);
        return true;
    }

    /**
     * @param mixed $rowId
     * @param mixed $newCount
     * 
     * @return 
     */
    public function updateRunningAdsCount($rowId, $newCount)
    { 
        return FbPage::where('id', $rowId)->update([
            'running_ads' => $newCount
        ]);
    }

     /**
     * @return [type]
     */
    public function getAll()
    {
        return FbPage::all();
    }

    public function getByPageId(string $pageId)
    { 
        return FbPage::where('page_id', $pageId)->first();
    }

    public function updateData($data, $rowId)
    {
        return FbPage::where('id', $rowId)->update($data);
    }

    public function getRandomFbPage()
    {
        return FbPage::where('running_ads', '<', 250)->first();
    }

    /**
     * @return array
     */
    public function groupPage(): array
    {
        $tot = FbPage::count();
        $groups = [];
        if ($tot > 0 && $tot <= 200) {
            $groups['Group 1'];
        }
        else {
            $dv = round($tot / 200, 1);
            for ($i = 1; $i <= $dv; $i++) { 
                array_push($groups, 'Group ' . $i);
            }
        }
        return $groups;
    }
}