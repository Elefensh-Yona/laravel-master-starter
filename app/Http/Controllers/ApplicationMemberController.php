<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationMemberRequest;
use App\Http\Requests\UpdateApplicationMemberRequest;
use App\Models\Application;
use App\Models\ApplicationMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationMemberController extends Controller
{
    public function index(Request $request, Application $application): Response
    {
        $this->authorize('view', $application);

        $members = $application->members()->with(['user', 'approver', 'ender'])->orderBy('joined_at')->get();

        return Inertia::render('applications/members/Index', [
            'application' => [
                'id' => $application->id,
                'programId' => $application->program_id,
                'primaryOwnerId' => $application->primary_owner_id,
                'status' => $application->status,
            ],
            'members' => $members->map(fn (ApplicationMember $member): array => [
                'id' => $member->id,
                'applicationId' => $member->application_id,
                'userId' => $member->user_id,
                'userName' => $member->user?->name,
                'status' => $member->status,
                'joinedAt' => $member->joined_at?->toIso8601String(),
                'approvedBy' => $member->approved_by,
                'endedAt' => $member->ended_at?->toIso8601String(),
            ])->values(),
            'canManage' => $request->user()?->can('update', $application) ?? false,
        ]);
    }

    public function store(StoreApplicationMemberRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('create', [ApplicationMember::class, $application]);

        $validated = $request->validated();
        $actor = $request->user();

        $member = DB::transaction(function () use ($application, $validated, $actor): ApplicationMember {
            return $application->members()->create([
                'user_id' => $validated['user_id'],
                'status' => $validated['status'] ?? 'active',
                'joined_at' => now(),
                'approved_by' => $actor->id,
            ]);
        });

        return to_route('applications.members.index', $application)
            ->with('success', 'Application member added successfully.');
    }

    public function update(UpdateApplicationMemberRequest $request, Application $application, ApplicationMember $member): RedirectResponse
    {
        $this->authorize('update', $member);

        $validated = $request->validated();

        $member->forceFill([
            'status' => $validated['status'] ?? $member->status,
            'ended_at' => $validated['status'] === 'ended' ? now() : null,
            'ended_by' => $validated['status'] === 'ended' ? $request->user()->id : null,
            'end_reason' => $validated['end_reason'] ?? null,
        ])->save();

        return to_route('applications.members.index', $application)
            ->with('success', 'Application member updated successfully.');
    }

    public function destroy(Request $request, Application $application, ApplicationMember $member): RedirectResponse
    {
        $this->authorize('delete', $member);

        $member->update([
            'status' => 'ended',
            'ended_at' => now(),
            'ended_by' => $request->user()->id,
            'end_reason' => 'removed_by_owner',
        ]);

        return to_route('applications.members.index', $application)
            ->with('success', 'Application member removed successfully.');
    }
}
