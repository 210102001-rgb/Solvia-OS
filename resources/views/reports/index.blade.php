@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-950 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Reports & Analytics</h1>
                <p class="text-slate-400 mt-2">Insights and data across your organization.</p>
            </div>
            <a href="{{ route('reports.index', ['type' => $reportType, 'from_date' => $dateRange['from'] ?? '', 'to_date' => $dateRange['to'] ?? '', 'export' => 'csv']) }}" class="bg-slate-800 hover:bg-slate-700 text-white font-medium px-4 py-2 rounded-xl transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export CSV
            </a>
        </div>

        <!-- Filters & Tabs -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 mb-8">
            <form action="{{ route('reports.index') }}" method="GET" class="flex flex-col lg:flex-row justify-between gap-4">
                <div class="flex gap-2 overflow-x-auto pb-2 lg:pb-0 hide-scrollbar">
                    <button type="submit" name="type" value="project_summary" class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ $reportType === 'project_summary' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">Project Summary</button>
                    <button type="submit" name="type" value="financial_summary" class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ $reportType === 'financial_summary' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">Financial Summary</button>
                    <button type="submit" name="type" value="team_performance" class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ $reportType === 'team_performance' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">Team Performance</button>
                    <button type="submit" name="type" value="resource_utilization" class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ $reportType === 'resource_utilization' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">Resource Utilization</button>
                </div>
                
                <div class="flex gap-2 items-center">
                    <input type="date" name="from_date" value="{{ $dateRange['from'] ?? '' }}" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                    <span class="text-slate-500">to</span>
                    <input type="date" name="to_date" value="{{ $dateRange['to'] ?? '' }}" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-3 py-2 rounded-xl text-sm transition-colors">Apply</button>
                </div>
            </form>
        </div>

        <!-- Report Content -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            @if($reportType === 'project_summary')
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-400">
                        <thead class="bg-slate-950 text-xs uppercase text-slate-500 border-b border-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-medium">Project</th>
                                <th class="px-6 py-4 font-medium">Health</th>
                                <th class="px-6 py-4 font-medium">Progress</th>
                                <th class="px-6 py-4 font-medium">Revenue</th>
                                <th class="px-6 py-4 font-medium">Expenses</th>
                                <th class="px-6 py-4 font-medium">Profit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($data as $row)
                            <tr class="hover:bg-slate-800/50">
                                <td class="px-6 py-4 text-white font-medium">{{ $row->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-medium {{ ($row->health ?? '') == 'good' ? 'bg-emerald-900/50 text-emerald-400' : 'bg-rose-900/50 text-rose-400' }}">
                                        {{ ucfirst($row->health ?? 'good') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-slate-800 rounded-full h-2 max-w-[100px]">
                                            <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $row->progress ?? 0 }}%"></div>
                                        </div>
                                        <span>{{ $row->progress ?? 0 }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-emerald-400">${{ number_format($row->revenue ?? 0, 2) }}</td>
                                <td class="px-6 py-4 text-rose-400">${{ number_format($row->expenses ?? 0, 2) }}</td>
                                <td class="px-6 py-4 text-white">${{ number_format(($row->revenue ?? 0) - ($row->expenses ?? 0), 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center">No data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @elseif($reportType === 'financial_summary')
                <div class="p-6">
                    <canvas id="financialChart" height="100"></canvas>
                </div>
                <div class="overflow-x-auto border-t border-slate-800">
                    <table class="w-full text-left text-sm text-slate-400">
                        <thead class="bg-slate-950 text-xs uppercase text-slate-500 border-b border-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-medium">Month</th>
                                <th class="px-6 py-4 font-medium text-right">Income</th>
                                <th class="px-6 py-4 font-medium text-right">Expense</th>
                                <th class="px-6 py-4 font-medium text-right">Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($data as $row)
                            <tr class="hover:bg-slate-800/50">
                                <td class="px-6 py-4 text-white font-medium">{{ $row['month'] ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-right text-emerald-400">${{ number_format($row['income'] ?? 0, 2) }}</td>
                                <td class="px-6 py-4 text-right text-rose-400">${{ number_format($row['expense'] ?? 0, 2) }}</td>
                                <td class="px-6 py-4 text-right text-white font-medium">${{ number_format(($row['income'] ?? 0) - ($row['expense'] ?? 0), 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center">No data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @elseif($reportType === 'team_performance')
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-400">
                        <thead class="bg-slate-950 text-xs uppercase text-slate-500 border-b border-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-medium">Team Member</th>
                                <th class="px-6 py-4 font-medium">Projects Involved</th>
                                <th class="px-6 py-4 font-medium">Tasks Completed</th>
                                <th class="px-6 py-4 font-medium">Avg Progress</th>
                                <th class="px-6 py-4 font-medium">Blockers Reported</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($data as $row)
                            <tr class="hover:bg-slate-800/50">
                                <td class="px-6 py-4 text-white font-medium">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-xs">{{ substr($row->name ?? 'U', 0, 2) }}</div>
                                        {{ $row->name ?? 'Unknown' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ $row->projects_count ?? 0 }}</td>
                                <td class="px-6 py-4">{{ $row->tasks_completed ?? 0 }}</td>
                                <td class="px-6 py-4">{{ $row->avg_progress ?? 0 }}%</td>
                                <td class="px-6 py-4 text-amber-500">{{ $row->blockers_reported ?? 0 }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center">No data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @elseif($reportType === 'resource_utilization')
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 border-b border-slate-800">
                    <div class="bg-slate-950 rounded-xl p-4 border border-slate-800">
                        <h4 class="text-slate-400 text-sm mb-1">Total Infra Cost</h4>
                        <p class="text-2xl font-bold text-white">${{ number_format($data['total_infra'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-slate-950 rounded-xl p-4 border border-slate-800">
                        <h4 class="text-slate-400 text-sm mb-1">Subscription Costs</h4>
                        <p class="text-2xl font-bold text-white">${{ number_format($data['total_subs'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-slate-950 rounded-xl p-4 border border-slate-800">
                        <h4 class="text-slate-400 text-sm mb-1">Active Assets</h4>
                        <p class="text-2xl font-bold text-white">{{ $data['active_assets'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-400">
                        <thead class="bg-slate-950 text-xs uppercase text-slate-500 border-b border-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-medium">Resource Type</th>
                                <th class="px-6 py-4 font-medium">Name</th>
                                <th class="px-6 py-4 font-medium">Assigned To</th>
                                <th class="px-6 py-4 font-medium">Cost / Mo</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($data['items'] ?? [] as $item)
                            <tr class="hover:bg-slate-800/50">
                                <td class="px-6 py-4">{{ $item->type ?? 'Asset' }}</td>
                                <td class="px-6 py-4 text-white font-medium">{{ $item->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $item->assigned_to ?? 'Unassigned' }}</td>
                                <td class="px-6 py-4">${{ number_format($item->cost ?? 0, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-emerald-900/50 text-emerald-400">Active</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center">No resource data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@if($reportType === 'financial_summary')
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('financialChart');
        if (ctx) {
            const data = @json($data);
            const labels = data.map(d => d.month);
            const income = data.map(d => d.income);
            const expense = data.map(d => d.expense);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Income',
                            data: income,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Expense',
                            data: expense,
                            borderColor: '#f43f5e',
                            backgroundColor: 'rgba(244, 63, 94, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { labels: { color: '#94a3b8' } }
                    },
                    scales: {
                        x: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } },
                        y: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } }
                    }
                }
            });
        }
    });
</script>
@endpush
@endif
