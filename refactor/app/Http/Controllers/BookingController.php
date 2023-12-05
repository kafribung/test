<?php

namespace DTApi\Http\Controllers;

// Grouping class
use Illuminate\Http\Request;
use DTApi\Models\{Job, Distance};
use DTApi\Repository\BookingRepository;
use DTApi\Http\Controllers\Trait\BookingMethodSupportTrait;


/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    use BookingMethodSupportTrait;

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * 
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $boooking_repository)
    {
        $this->repository = $boooking_repository;
    }

    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        if ($user_id = $request->user_id)
            $response = $this->repository->getUsersJobs($user_id);
        elseif ($this->isAdminOrSuperAdmin(auth()->user()))
            $response = $this->repository->getAll($request);

        return response($response);
    }

    /**
     * Display the specified resource.
     * 
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        return response($this->repository->with('translatorJobRel.user')->find($id));
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        return response($this->repository->store(auth()->user(), $request->all()));
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request): mixed
    {
        $response = $this->repository->updateJob($id, array_except($request->all(), ['_token', 'submit']), auth()->user());

        return response($response);
    }

    /**
     * Store job email
     * 
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request): mixed
    {
        return response($this->repository->storeJobEmail($request->all()));
    }

    /**
     * get job user history
     * 
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request): mixed
    {
        $user_id = $request->user_id;

        // If user_id null or false
        if (!$user_id)
            return null;

        return response($this->repository->getUsersJobsHistory($user_id, $request));
    }

    /**
     * accept job
     * 
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request): mixed
    {
        return response($this->repository->acceptJob($request->all(), auth()->user()));
    }


    /**
     * accept job with id
     * 
     * @param Request $request
     * @return mixed
     */
    public function acceptJobWithId(Request $request): mixed
    {
        return response($this->repository->acceptJobWithId($request->job_id, auth()->user()));
    }

    /**
     * cancel job
     * 
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request): mixed
    {
        return response($this->repository->cancelJobAjax($request->all(), auth()->user()));
    }

    /**
     * end job
     * 
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request): mixed
    {
        return response($this->repository->endJob($request->all()));
    }

    /**
     * customer not call
     * 
     * @param Request $request
     * @return mixed
     */
    public function customerNotCall(Request $request): mixed
    {
        return response($this->repository->customerNotCall($request->all()));
    }

    /**
     * get potential jobs
     * 
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs()
    {
        return response($this->repository->getPotentialJobs(auth()->user()));
    }

    /**
     * distance feed
     * 
     * @param Request $request
     * @return mixed
     */
    public function distanceFeed(Request $request): mixed
    {
        $data = $request->all();

        $distance = $this->getValueOrDefault($data, 'distance');
        $time = $this->getValueOrDefault($data, 'time');
        $jobid = $this->getValueOrDefault($data, 'jobid');
        $session = $this->getValueOrDefault($data, 'session_time');
        $flagged = $this->getFlaggedValue($data);
        $manually_handled = $this->getBooleanValue($data, 'manually_handled');
        $by_admin = $this->getBooleanValue($data, 'by_admin');
        $admincomment = $this->getValueOrDefault($data, 'admincomment');

        if ($time || $distance)
            Distance::where('job_id', $jobid)->update(['distance' => $distance, 'time' => $time]);

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', $jobid)->update([
                'admin_comments' => $admincomment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manually_handled,
                'by_admin' => $by_admin
            ]);
        }

        return response('Record updated!');
    }

    /**
     * reopen
     * 
     * @param Request $request
     * @return mixed
     */
    public function reopen(Request $request): mixed
    {
        return response($this->repository->reopen($request->all()));
    }

    /**
     * resend notifications
     * 
     * @param Request $request
     * @return mixed
     */
    public function resendNotifications(Request $request): mixed
    {
        $repository = $this->repository;

        $job = $repository->find($request->jobid);
        $job_data = $repository->jobToData($job);
        $repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $repository = $this->repository;

        $job = $repository->find($request->jobid);
        $repository->jobToData($job);

        try {
            $repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }


}
