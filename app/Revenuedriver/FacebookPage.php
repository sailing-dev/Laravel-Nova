<?php

namespace App\Revenuedriver;

use App\Revenuedriver\Base\Facebook;
use App\Services\AdAccountService;
use App\Services\FbPageService;
use FacebookAds\Object\Ad;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\AdSet;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\Page;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\Business;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookPage extends Facebook
{
    /**
     * @var int
     */
    protected $createAttempts = 0;

    /**
     * @var int
     */
    protected $deleteAttempts = 0;

    /**
     * @var int
     */
    protected $loadInstragramAccountAttempts = 0;
    public $appId;

    public $clientSecret;

    public $clientToken;
 
    /**
     * @var string
     */
    public $api;

 
    /**
     * @param string $accountId
     * @param array $params
     * @param array $fields=[]
     * 
     * @return array
     */
    public function loadInstagramAccounts(string $pageId, array $params=[], array $fields=[]): array
    { 
       
        $longLivedUserAccessToken = $this->getLongLivedUserAccessToken();

        $pageAccessToken = $this->getPageAccessToken($pageId, $longLivedUserAccessToken, hash_hmac('sha256', $longLivedUserAccessToken, $this->appSecret));

        $proof= hash_hmac('sha256', $pageAccessToken, $this->appSecret); 
     
        if ($pageAccessToken != null) {
           
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                ])
                ->post('https://graph.facebook.com/v10.0/'.$pageId.'/page_backed_instagram_accounts', [
                    'access_token' => $pageAccessToken,
                    'fields' => ['id'],
                    'appsecret_proof' => $proof
                ]);

                $decoded = json_decode($response->body());
               
                if (isset($decoded->id)) {
                    return [true, $decoded->id];
                }
               if (isset($decoded->error)) {
                  return [false, $decoded->error->message];
               }
               return [false, 'Unknown error'];
            } catch(\FacebookAds\Exception\Exception | \FacebookAds\Http\Exception\ClientException | \FacebookAds\Http\Exception\EmptyResponseException |
                \FacebookAds\Http\Exception\ServerException | \FacebookAds\Http\Exception\RequestException
                | \FacebookAds\Http\Exception\ThrottleException  | \FacebookAds\Http\Exception\PermissionException
                | \FacebookAds\Http\Exception\AuthorizationException $e) 
            {
                if ($this->loadInstragramAccountAttempts < 10) {
                    sleep(3);
                    $this->loadInstragramAccountAttempts++;
                    return $this->loadInstagramAccounts($pageId, $params, $fields);
                } 
                return [false, $e];
            }
            catch (\Throwable $th) {
                return [false, $th];
            }
        }
        return [false, 'No record returned'];
    }


    /**
     * @return string 
     */
    protected function getLongLivedUserAccessToken(): string
    {
        $shortUserAccessToken = 'EAAFmyiUTy1IBAGElSLWPOdlDWSlVJdivOcodrPVT1XAx526MX3eLT1qYKEGyMkODzvnhI9JVJOuYuR8pqvndx8SsW8YtWZCz1xWmxR3poRzxKoEPqOVZCwitu9cOkQQtx4XapGAD28TiWRMmZAvCi9Tx6k3grUUN2X3KAu4fZCcFEQcKgnfm0I0ZADystpKBZCCgJjg5ZBIZAPBUBwM608zrIdsTedUsQZBkbUzgTRMA1XQZDZD';
         
        // Last fetched: 31 march, 2021 10:10PM UTC
        $longUserAccessToken='EAAFmyiUTy1IBALiQZBTX2E9wHnZBv2stzDJ2oFWD9skzFa6GalC4dwlIqiPb6da8zQxyLBvJNYjcRUszfNvBJ3pQk6fZC11Ny7hNGeGWr5WqmpEQihsKGjWtDSJ9uxFITJAShkNZC4JO7ZB8WqKbkVf44DJq2KVJ9ob0BY1IQLQZDZD';
       
        // try {
        //     $response = Http::withHeaders([
        //         'Accept' => 'application/json',
        //         'Content-type' => 'application/json',
        //     ])
        //     ->get('https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id=' . $this->appId . 
        //     '&client_secret='.$this->appSecret. '&fb_exchange_token=' . $shortUserAccessToken);

        //     $decoded = json_decode($response->body());
        //     dd('Long lived user access token', $decoded);
        //     return $decoded->access_token;
        // } catch (\Throwable $th) {
        //     dd($th);
        //     return false;
        // }
        return $longUserAccessToken;
    }

    protected function getPageAccessToken($pageId, $longLivedUserAccessToken, $proof)
    {
         // get a page access token
         try {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ])->get('https://graph.facebook.com/'.$pageId.'?access_token='.$longLivedUserAccessToken.'&fields=access_token&appsecret_proof='.$proof);

            $decoded = json_decode($response->body());
            if (isset($decoded->access_token)) {
                return $decoded->access_token;
            }
            // dd($decoded);
            return null;
        } catch (\Throwable $th) {
            return null;
        }
    }


    /**
     * @return void
     */
    public function loadBusinessAccountPages()
    {
         
        $businessManagers = [
            [
                'id' => '137338727436558',
                'name' => 'rd'
            ], 
            [
                'id' => '276611900308096',
                'name' => 'tt'
            ]
        ];

        foreach ($businessManagers as $businessManager) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                ])->get('https://graph.facebook.com/'.$businessManager['id'].'/owned_pages?access_token=' . $this->getLongLivedUserAccessToken() . 
                '&appsecret_proof='.hash_hmac('sha256', $this->getLongLivedUserAccessToken(), $this->appSecret) . '&limit=60000');
    
                $decoded = json_decode($response->body()); 
                if (isset($decoded->data)) {
                    if (count($decoded->data) > 0) {
                        $fbPageService = new FbPageService;
                        
                        foreach ($decoded->data as $page) {
                            
                            $pageRow = $fbPageService->getByPageId($page->id);
                           
                            if ($pageRow == null || 
                                ( $pageRow != null && $pageRow->instagram_id == null)  ) {
                                    // load the instagram ID for the page
                                   
                                    if ($page->id != '112005480631100') {
                                       
                                        $pageIgAccountId = $this->loadInstagramAccounts($page->id);
                                       
                                        if ($pageIgAccountId[0] == true) { 
                                            if ($pageRow == null) {
                                                $fbPageService->create($page->name, $page->id, $pageIgAccountId[1], $businessManager['name']);
                                            }
                                            else {
                                                $fbPageService->updateData([
                                                    'instagram_id' => $pageIgAccountId[1]
                                                ], $pageRow->id);
                                                Log::info('Updated', [$pageRow->page_name, $pageIgAccountId[1]]);
                                            }
                                           
                                        }
                                    }
                                    
                            }
                        }
 
                    }
                } 
            } catch (\Throwable $th) {
                Log::error('An error occured while processing the loadBusinessAccountPages call', [$th]);
            }
        }
    }


   
    /**
     * @param int $rowId
     * @param string $pageId
     * 
     * @return bool
     */
    public function curateRunningAds(int $rowId, string $pageId): bool
    {
        $fbPageService = new FbPageService;
        $inst = new FacebookAdAccount(); 
        $adAccountId = $this->getAccount21Id();  
        $pull = $inst->getAdsVolume($adAccountId, [
            'ads_running_or_in_review_count'
        ], [
            'page_id' => $pageId
        ]); 
        if ($pull[0] == true) { 
            if (count($pull[1]) > 0) {
                $newCount = isset($pull[1][0]->ads_running_or_in_review_count) ? 
                    $pull[1][0]->ads_running_or_in_review_count : null;

                $fbPageService->updateRunningAdsCount($rowId, $newCount);
                return true;
            } 
        } 
        return false;
    }


   
    /**
     * @param array $fields
     * @param string $businessManagerId
     * 
     * @return array
     */
    public function createPage(array $fields, string $businessManagerId): array
    {
        
        try { 
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ])->post('https://graph.facebook.com/'.$businessManagerId.'/accounts?access_token=' . $this->getLongLivedUserAccessToken() . 
            '&appsecret_proof='.hash_hmac('sha256', $this->getLongLivedUserAccessToken(), $this->appSecret), [
                'name' => $fields['name'],
                'about' => $fields['about'],
                'category_enum' => $fields['category_enum'],
                'category_list' => $fields['category_list'],
                'city_id' => $fields['city_id'],
                'cover_photo' => $fields['cover_photo'],
                'description' => $fields['description'],
                'picture' => $fields['picture']
            ]);
            $decoded = json_decode($response->body());
            return [true];
        } catch (\Throwable $th) {
            Log::error('An error occured while dynmaically creating the facebook page', [$th]);
            return [false, $th->getMessage()];
        }
    }
}