<?php

namespace App\Http\Controllers\FbReporting;

use App\Http\Controllers\Controller;
use App\Http\Requests\FbReporting\FbPagePost\DeletePostRequest;
use App\Http\Requests\FbReporting\FbPagePost\SubmitPostRequest;
use App\Http\Requests\FbReporting\FbPagePost\UpdatePostRequest;
use App\Http\Resources\FbReporting\FbPagePostResource;
use App\Http\Resources\FbReporting\FbPagePostSchedulerResource;
use App\Labs\FileManager;
use App\Revenuedriver\FacebookPage;
use App\Services\FbReporting\FbPagePostSchedulerService;
use App\Services\FbReporting\FbPagePostService;
use Illuminate\Http\Request;

class FbPagePostsController extends Controller
{
    /**
     * @param SubmitPostRequest $request
     * @param FbPagePostService $fbPagePostService
     * @param FbPagePostSchedulerService $fbPagePostSchedulerService
     * @param FileManager $fileManager
     * 
     * @return JsonResponse
     */
    public function submit(SubmitPostRequest $request, FbPagePostService $fbPagePostService, FbPagePostSchedulerService $fbPagePostSchedulerService, FileManager $fileManager)
    {
        $media = null;
        
        if ($request->media !== null && $request->file('media')->isValid()) {
            $uploadImage = $fileManager->uploadFile($request->media, 'media', 'fb_posts');
            if (!$uploadImage[0]) {
                return $this->errorResponse('File was not uploaded successfully: ' . $uploadImage[1], [], 400);
            }
            $media = $uploadImage[1];
        }
        
        $newPost = $fbPagePostService->create([
            'text' => $request->text,
            'url' => $request->url,
            'reference' => $request->reference,
            'media' => $media
        ]);
        if ($newPost[0] !== true) {
            return $this->errorResponse('An error occured. Please try again');
        } 
        if ($request->start_date != null && gettype($request->page_groups) === 'array' && count($request->page_groups) > 0) {
            $startDate = gettype($request->start_date) === 'array' ? $request->start_date['date'] : $request->start_date;
            $fbPagePostSchedulerService->createSchedule([
                'start_date' => $startDate,
                'fb_page_post_id' => $newPost[1]->id,
                'page_groups' => $request->page_groups
            ]);
        }
        return $this->successResponse('New post has been successfully submitted');
    }

    public function loadLibrary(Request $request,  FbPagePostService $fbPagePostService)
    {
        return $this->successResponse(
            'Data returned successfully',
            FbPagePostResource::collection($fbPagePostService->loadLibrary())
        );
    }


    /**
     * @param SubmitPostRequest $request
     * @param FbPagePostService $fbPagePostService
     * @param FbPagePostSchedulerService $fbPagePostSchedulerService
     * @param FileManager $fileManager
     * 
     * @return JsonResponse
     */
    public function update(UpdatePostRequest $request, FbPagePostService $fbPagePostService, FbPagePostSchedulerService $fbPagePostSchedulerService, FileManager $fileManager)
    {
        $media = null;
        $data = [
            'text' => $request->text,
            'url' => $request->url,
            'reference' => $request->reference
        ];
        if ($request->media !== null && $request->file('media')->isValid()) {
            $uploadImage = $fileManager->uploadFile($request->media, 'media', 'fb_posts');
            if (!$uploadImage[0]) {
                return $this->errorResponse('File was not uploaded successfully: ' . $uploadImage[1], [], 400);
            }
            $data['media'] = $uploadImage[1];
        }

        $updatePost = $fbPagePostService->update($data, $request->fb_page_post_id);
        if ($updatePost[0] !== true) {
            return $this->errorResponse('An error occured. Please try again');
        }
        $ret = null;
        if (
            isset($request->fb_page_post_scheduler_id) && $request->fb_page_post_scheduler_id != null &&
            isset($request->page_groups) && count($request->page_groups) > 0 && 
            isset($request->start_date)
        ) { 
           
            $startDate = gettype($request->start_date) === 'array' ? $request->start_date['date'] : $request->start_date;
            $schedule = $fbPagePostSchedulerService->updateSchedule([
                'start_date' => $startDate,
                'fb_page_post_id' => $updatePost[1]->id,
                'page_groups' => $request->page_groups
            ], $request->fb_page_post_scheduler_id);

            if ($schedule[0] === false) {
                return $this->errorResponse('An error occured while updating the schedule. Please try again');
            } else {
                if ($request->has('return_scheduler_resource') && $request->return_scheduler_resource == true) {
                    $ret = new FbPagePostSchedulerResource($schedule[1]);
                }
            }
        }
        if ($ret == null) {
            $ret = new FbPagePostResource($updatePost[1]);
        }
        return $this->successResponse('Update was successful', $ret);
    }

    /**
     * @param DeletePostSchedulerRequest $request
     * @param FbPagePostSchedulerService $fbPagePostSchedulerService
     * 
     * @return JsonResponse
     */
    public function delete(DeletePostRequest $request, FbPagePostService $fbPagePostService)
    {
        if (!$fbPagePostService->deleteRow($request->id)) {
            return $this->errorResponse('An error occured. Please try again');
        }
        return $this->successResponse('Post has been successfully deleted');
    }
}
