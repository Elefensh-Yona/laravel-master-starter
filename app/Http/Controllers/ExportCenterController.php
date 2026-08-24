<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Spatie\ArrayToXml\ArrayToXml;
use Spatie\Permission\Models\Role;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportCenterController extends Controller
{
    /**
     * Display reusable export and print actions.
     */
    public function index(Request $request): InertiaResponse
    {
        $resources = [];

        if ($request->user()?->can('users.view')) {
            $resources[] = [
                'key' => 'users-csv',
                'title' => 'Users export',
                'description' => 'Download a CSV snapshot of users and their assigned roles.',
                'href' => route('exports.users.csv'),
                'actionLabel' => 'Download CSV',
                'format' => 'CSV',
            ];

            $resources[] = [
                'key' => 'users-xlsx',
                'title' => 'Users spreadsheet',
                'description' => 'Download an XLSX snapshot of users and their assigned roles.',
                'href' => route('exports.users.xlsx'),
                'actionLabel' => 'Download XLSX',
                'format' => 'XLSX',
            ];

            $resources[] = [
                'key' => 'users-xml',
                'title' => 'Users XML feed',
                'description' => 'Download an XML snapshot of users and their assigned roles.',
                'href' => route('exports.users.xml'),
                'actionLabel' => 'Download XML',
                'format' => 'XML',
            ];
        }

        $resources[] = [
            'key' => 'workspace-print',
            'title' => 'Workspace summary print',
            'description' => 'Open a print-friendly summary of core workspace counts and recent activity.',
            'href' => route('exports.summary.print'),
            'actionLabel' => 'Open print view',
            'format' => 'Print',
        ];

        $resources[] = [
            'key' => 'workspace-pdf',
            'title' => 'Workspace summary PDF',
            'description' => 'Download a PDF snapshot of core workspace counts and recent activity.',
            'href' => route('exports.summary.pdf'),
            'actionLabel' => 'Download PDF',
            'format' => 'PDF',
        ];

        return Inertia::render('exports/Index', [
            'resources' => $resources,
        ]);
    }

    /**
     * Download a CSV export of users.
     */
    public function usersCsv(Request $request): StreamedResponse
    {
        ActivityLogger::record(
            actor: $request->user(),
            event: 'exports.users-csv',
            description: 'Downloaded the users CSV export.',
            properties: [
                'format' => 'csv',
            ],
            request: $request,
        );

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Name', 'Email', 'Roles', 'Email Verified At', 'Created At']);

            foreach ($this->userExportRows() as $row) {
                fputcsv($handle, array_values($row));
            }

            fclose($handle);
        }, 'users-export.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Download an XLSX export of users.
     */
    public function usersXlsx(Request $request): StreamedResponse
    {
        ActivityLogger::record(
            actor: $request->user(),
            event: 'exports.users-xlsx',
            description: 'Downloaded the users XLSX export.',
            properties: [
                'format' => 'xlsx',
            ],
            request: $request,
        );

        return response()->streamDownload(function (): void {
            SimpleExcelWriter::createWithoutBom('php://output', 'xlsx')
                ->addHeader(['Name', 'Email', 'Roles', 'Email Verified At', 'Created At'])
                ->addRows($this->userExportRows())
                ->close();
        }, 'users-export.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Download an XML export of users.
     */
    public function usersXml(Request $request): Response
    {
        ActivityLogger::record(
            actor: $request->user(),
            event: 'exports.users-xml',
            description: 'Downloaded the users XML export.',
            properties: [
                'format' => 'xml',
            ],
            request: $request,
        );

        $xml = ArrayToXml::convert([
            'user' => array_map(
                fn (array $row): array => array_combine(
                    ['name', 'email', 'roles', 'email_verified_at', 'created_at'],
                    array_values($row),
                ),
                $this->userExportRows(),
            ),
        ], 'users');

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * Shared row payload for the CSV/XLSX/XML user exports.
     *
     * @return list<array{Name: string, Email: string, Roles: string, 'Email Verified At': string|null, 'Created At': string|null}>
     */
    private function userExportRows(): array
    {
        return User::query()
            ->with('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'Name' => $user->name,
                'Email' => $user->email,
                'Roles' => $user->roles->pluck('name')->join(', '),
                'Email Verified At' => $user->email_verified_at?->toDateTimeString(),
                'Created At' => $user->created_at?->toDateTimeString(),
            ])
            ->values()
            ->all();
    }

    /**
     * Show a print-friendly summary page.
     */
    public function printSummary(Request $request): InertiaResponse
    {
        ActivityLogger::record(
            actor: $request->user(),
            event: 'exports.summary-print',
            description: 'Opened the workspace print summary.',
            properties: [
                'format' => 'print',
            ],
            request: $request,
        );

        return Inertia::render('exports/PrintSummary', [
            'summary' => $this->buildSummary($request),
        ]);
    }

    /**
     * Download a PDF version of the workspace summary.
     */
    public function summaryPdf(Request $request): Response
    {
        ActivityLogger::record(
            actor: $request->user(),
            event: 'exports.summary-pdf',
            description: 'Downloaded the workspace summary PDF.',
            properties: [
                'format' => 'pdf',
            ],
            request: $request,
        );

        $pdf = Pdf::loadView('exports.summary', [
            'actor' => $request->user(),
            'summary' => $this->buildSummary($request),
            'generatedAt' => now()->toDateTimeString(),
        ]);

        return $pdf->download('workspace-summary.pdf');
    }

    /**
     * Assemble the shared workspace summary payload for print and PDF.
     *
     * @return array{counts: array{users: int, roles: int, unreadNotifications: int, activityLogs: int}, recentUsers: list<array{id: int, name: string, email: string, roles: list<string>, createdAt: string|null}>, recentEvents: list<array{id: int, event: string, description: string, createdAt: string|null}>}
     */
    private function buildSummary(Request $request): array
    {
        return [
            'counts' => [
                'users' => User::query()->count(),
                'roles' => Role::query()->count(),
                'unreadNotifications' => $request->user()?->unreadNotifications()->count() ?? 0,
                'activityLogs' => ActivityLog::query()->count(),
            ],
            'recentUsers' => User::query()
                ->with('roles')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->values()->all(),
                    'createdAt' => $user->created_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'recentEvents' => ActivityLog::query()
                ->latest('created_at')
                ->limit(6)
                ->get()
                ->map(fn (ActivityLog $log): array => [
                    'id' => $log->id,
                    'event' => $log->event,
                    'description' => $log->description,
                    'createdAt' => $log->created_at?->toISOString(),
                ])
                ->values()
                ->all(),
        ];
    }
}
