<?php

namespace Modules\UserManagement\Http\Controllers\Web\New\Admin\DriverApplication;

use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\UserManagement\Entities\DriverApplication;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriverApplicationController extends Controller
{
    use AuthorizesRequests;

    private const DISK = 'local';

    private const DOC_LABELS = [
        'driver_license_front' => "Driver's License (Front)",
        'driver_license_back'  => "Driver's License (Back)",
        'insurance_card'       => 'Insurance Card',
        'selfie'               => 'Selfie / Photo',
    ];

    public function index(Request $request): View
    {
        $this->authorize('user_view');

        $query = DriverApplication::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(25);

        return view('usermanagement::admin.driver-application.index', compact('applications'));
    }

    public function show(string $id): View|RedirectResponse
    {
        $this->authorize('user_view');

        $application = DriverApplication::with('reviewer')->find($id);

        if (!$application) {
            Toastr::warning('Application not found.');
            return redirect()->route('admin.driver-applications.index');
        }

        $docLabels = self::DOC_LABELS;

        return view('usermanagement::admin.driver-application.show', compact('application', 'docLabels'));
    }

    public function approve(string $id): RedirectResponse
    {
        $this->authorize('user_edit');

        $application = DriverApplication::findOrFail($id);
        $application->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Toastr::success('Application approved.');
        return back();
    }

    public function reject(Request $request, string $id): RedirectResponse
    {
        $this->authorize('user_edit');

        $request->validate([
            'reject_reason' => 'required|string|max:2000',
        ], [
            'reject_reason.required' => 'A reason is required when rejecting an application.',
        ]);

        $application = DriverApplication::findOrFail($id);
        $application->update([
            'status'        => 'rejected',
            'reject_reason' => $request->input('reject_reason'),
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        Toastr::success('Application rejected.');
        return back();
    }

    /**
     * Serve a document file securely (admin-only).
     */
    public function serveDocument(string $id, string $docKey): StreamedResponse|RedirectResponse
    {
        $this->authorize('user_view');

        $application = DriverApplication::findOrFail($id);

        $doc = $application->getDoc($docKey);

        if (!$doc || empty($doc['path'])) {
            Toastr::error('Document not found.');
            return back();
        }

        if (!Storage::disk(self::DISK)->exists($doc['path'])) {
            Toastr::error('Document file is missing from storage.');
            return back();
        }

        $downloadName = $doc['original_name'] ?? ($docKey . '.jpg');
        $mime = $doc['mime'] ?? 'application/octet-stream';

        return Storage::disk(self::DISK)->response($doc['path'], $downloadName, [
            'Content-Type' => $mime,
        ]);
    }

    /**
     * Legacy: download the single license photo for backward compat.
     */
    public function downloadLicense(string $id): StreamedResponse|RedirectResponse
    {
        $this->authorize('user_view');

        $application = DriverApplication::findOrFail($id);

        // Try new docs first, fall back to legacy column
        $frontDoc = $application->getDoc('driver_license_front');
        if ($frontDoc && !empty($frontDoc['path']) && Storage::disk(self::DISK)->exists($frontDoc['path'])) {
            $downloadName = $frontDoc['original_name'] ?? 'license-photo';
            return Storage::disk(self::DISK)->download($frontDoc['path'], $downloadName);
        }

        if ($application->license_photo_path) {
            // Legacy: stored on public disk
            if (Storage::disk('public')->exists($application->license_photo_path)) {
                $downloadName = $application->license_photo_original_name ?: 'license-photo';
                return Storage::disk('public')->download($application->license_photo_path, $downloadName);
            }
        }

        Toastr::error('License photo not found.');
        return back();
    }
}
