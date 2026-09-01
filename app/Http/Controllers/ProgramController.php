<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProgramRequest;
use App\Models\Program;
use App\Models\ProgramMembership;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\SystemRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Program::class);

        $actor = $request->user();

        $programs = Program::query()
            ->when(
                $actor?->hasRole(SystemRole::SUPER_ADMIN),
                fn (Builder $query): Builder => $query,
                function (Builder $query) use ($actor): void {
                    $query->where('status', 'published');

                    if ($actor?->can('program.view')) {
                        $query->orWhereHas('memberships', function (Builder $membershipQuery) use ($actor): void {
                            $membershipQuery
                                ->where('user_id', $actor->id)
                                ->where('status', 'active');
                        });
                    }
                },
            )
            ->orderBy('name')
            ->get()
            ->map(fn (Program $program): array => $this->programSummary($program, $actor))
            ->values();

        return Inertia::render('programs/Index', [
            'programs' => $programs,
            'canCreate' => $actor?->can('create', Program::class) ?? false,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Program::class);

        return Inertia::render('programs/Create');
    }

    public function store(SaveProgramRequest $request): RedirectResponse
    {
        $this->authorize('create', Program::class);

        $validated = $request->validated();
        $actor = $request->user();

        $program = DB::transaction(function () use ($actor, $validated): Program {
            $program = Program::query()->create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'slug' => $validated['slug'],
                'status' => 'draft',
                'timezone' => $validated['timezone'],
                'opens_at' => $validated['opens_at'],
                'closes_at' => $validated['closes_at'],
                'created_by' => $actor->id,
                'description' => $validated['description'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
            ]);

            ProgramMembership::query()->create([
                'program_id' => $program->id,
                'user_id' => $actor->id,
                'capability' => 'program_staff',
                'status' => 'active',
                'starts_at' => now(),
                'granted_by' => $actor->id,
            ]);

            return $program;
        });

        ActivityLogger::record(
            actor: $actor,
            event: 'programs.created',
            description: "Created program {$program->name}.",
            subject: $program,
            request: $request,
        );

        return to_route('programs.show', $program)->with('success', 'Program created successfully.');
    }

    public function show(Request $request, Program $program): Response
    {
        $this->authorize('view', $program);

        return Inertia::render('programs/Show', [
            'program' => $this->programSummary($program, $request->user()),
        ]);
    }

    public function edit(Request $request, Program $program): Response
    {
        $this->authorize('update', $program);

        return Inertia::render('programs/Edit', [
            'program' => $this->programSummary($program, $request->user()),
        ]);
    }

    public function update(SaveProgramRequest $request, Program $program): RedirectResponse
    {
        $this->authorize('update', $program);

        $program->update($request->validated());

        ActivityLogger::record(
            actor: $request->user(),
            event: 'programs.updated',
            description: "Updated program {$program->name}.",
            subject: $program,
            request: $request,
        );

        return to_route('programs.show', $program)->with('success', 'Program updated successfully.');
    }

    public function publish(Request $request, Program $program): RedirectResponse
    {
        $this->authorize('publish', $program);

        $program->forceFill([
            'status' => 'published',
            'published_at' => now(),
        ])->save();

        ActivityLogger::record(
            actor: $request->user(),
            event: 'programs.published',
            description: "Published program {$program->name}.",
            subject: $program,
            request: $request,
        );

        return to_route('programs.show', $program)->with('success', 'Program published successfully.');
    }

    /**
     * @return array{id: int, name: string, code: string, slug: string, status: string, timezone: string, opensAt: string, closesAt: string, description: string|null, publishedAt: string|null, canEdit: bool, canPublish: bool}
     */
    private function programSummary(Program $program, ?User $actor): array
    {
        return [
            'id' => $program->id,
            'name' => $program->name,
            'code' => $program->code,
            'slug' => $program->slug,
            'status' => $program->status,
            'timezone' => $program->timezone,
            'opensAt' => $program->opens_at->toIso8601String(),
            'closesAt' => $program->closes_at->toIso8601String(),
            'description' => $program->description,
            'publishedAt' => $program->published_at?->toIso8601String(),
            'canEdit' => $actor?->can('update', $program) ?? false,
            'canPublish' => $actor?->can('publish', $program) ?? false,
        ];
    }
}
