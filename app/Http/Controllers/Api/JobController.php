<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    // GET /api/jobs/available
    public function available(Request $request)
    {
        $user = Auth::user();

        // later: filter by driver prefs, location, etc.
        $jobs = Job::where('status', 'available')
            ->orderBy('pickup_time', 'asc')
            ->limit(50)
            ->get();

        return response()->json($jobs);
    }

    // GET /api/jobs/my
    public function myJobs(Request $request)
    {
        $user = Auth::user();

        $jobs = Job::where('driver_id', $user->id)
            ->orderBy('pickup_time', 'desc')
            ->get();

        return response()->json($jobs);
    }

    // GET /api/jobs/{job}
    public function show(Job $job)
    {
        return response()->json($job);
    }

    // POST /api/jobs/{job}/accept
    public function accept(Job $job)
    {
        $user = Auth::user();

        if ($job->status !== 'available') {
            return response()->json([
                'message' => 'Job is no longer available.'
            ], 400);
        }

        $job->driver_id = $user->id;
        $job->status = 'accepted';
        $job->save();

        return response()->json($job);
    }

    // POST /api/jobs/{job}/start
    public function start(Job $job)
    {
        $user = Auth::user();

        if ($job->driver_id !== $user->id) {
            return response()->json(['message' => 'Not your job.'], 403);
        }

        if (!in_array($job->status, ['accepted', 'picked_up'])) {
            return response()->json(['message' => 'Job cannot be started.'], 400);
        }

        $job->status = 'started';
        $job->save();

        return response()->json($job);
    }

    // POST /api/jobs/{job}/pickup
    public function pickup(Job $job)
    {
        $user = Auth::user();

        if ($job->driver_id !== $user->id) {
            return response()->json(['message' => 'Not your job.'], 403);
        }

        if ($job->status !== 'started') {
            return response()->json(['message' => 'Job must be started before pickup.'], 400);
        }

        $job->status = 'picked_up';
        $job->save();

        return response()->json($job);
    }

    // POST /api/jobs/{job}/complete
    public function complete(Job $job)
    {
        $user = Auth::user();

        if ($job->driver_id !== $user->id) {
            return response()->json(['message' => 'Not your job.'], 403);
        }

        if (!in_array($job->status, ['started', 'picked_up'])) {
            return response()->json(['message' => 'Job cannot be completed.'], 400);
        }

        $job->status = 'completed';
        $job->save();

        // later: create payout record, update driver_monthly_earnings here

        return response()->json($job);
    }

    // POST /api/jobs/{job}/cancel
    public function cancel(Job $job)
    {
        $user = Auth::user();

        if ($job->driver_id !== null && $job->driver_id !== $user->id) {
            return response()->json(['message' => 'Not your job.'], 403);
        }

        $job->status = 'canceled';
        $job->save();

        return response()->json($job);
    }
}
