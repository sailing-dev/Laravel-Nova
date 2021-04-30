<?php 

namespace App\Services\FbReporting;

use App\Models\FbReporting\FbPagePost;

class FbPagePostService
{
    /**
     * @param array $data
     * 
     * @return array
     */
    public function create(array $data): array
    {
        $fbPagePost = new FbPagePost;
        $fbPagePost->text = $data['text'];
        $fbPagePost->url = $data['url'];
        $fbPagePost->reference = $data['reference'];
        $fbPagePost->media = $data['media'];
        if ($fbPagePost->save()) {
            return [true, $fbPagePost];
        }
        return [false];
    }
}