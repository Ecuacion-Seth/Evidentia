@extends('layouts.app', ['title' => 'Academic Paper Submissions History'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#0F172A] dark:text-[#F1F5F9] tracking-tight">Academic Submissions History</h1>
            <p class="text-sm text-[#64748B] dark:text-[#94A3B8] mt-1">Review evidence integrity reports, practical coverage ratios, and student revision audits.</p>
        </div>
        <a href="{{ route('papers.create') }}"
            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-xs tracking-wide shadow-xs transition-colors flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Paper Scan
        </a>
    </div>

    <!-- Table of Papers -->
    <div class="bg-white dark:bg-[#111C35] border border-[#E2E8F0] dark:border-[#1E293B] rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F8FAFC] dark:bg-[#0B1120] text-[#64748B] dark:text-[#94A3B8] uppercase tracking-wider text-[10px] border-b border-[#E2E8F0] dark:border-[#1E293B] font-semibold">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Document Title</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Practical Coverage</th>
                        <th class="py-3.5 px-4">Audited Claims</th>
                        <th class="py-3.5 px-4">Submitted</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]/70 dark:divide-[#1E293B]/70 text-[#0F172A] dark:text-[#F1F5F9]">
                    @forelse ($papers as $p)
                        <tr class="hover:bg-[#F8FAFC]/70 dark:hover:bg-[#0B1120]/50 transition-colors">
                            <td class="py-4 px-4 sm:px-6">
                                <a href="{{ route('papers.show', $p) }}" class="font-serif font-semibold text-[#0F172A] dark:text-[#F1F5F9] hover:text-blue-600 dark:hover:text-blue-400 transition-colors block text-sm sm:text-base">
                                    {{ $p->title }}
                                </a>
                                <span class="text-[11px] text-[#64748B] dark:text-[#94A3B8]">{{ $p->original_filename }} &bull; ID #{{ $p->id }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider
                                    {{ $p->status === 'completed' ? 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-400 border border-emerald-500/20' : ($p->status === 'failed' ? 'bg-rose-500/10 text-rose-800 dark:text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-800 dark:text-amber-400 border border-amber-500/20') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-mono font-semibold text-emerald-700 dark:text-emerald-400">
                                {{ number_format($p->practical_coverage, 1) }}% OA
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1.5 font-medium text-[11px]">
                                    <span class="text-emerald-700 dark:text-emerald-400" title="Supported">✓ {{ $p->claims->where('verdict', 'SUPPORTED')->count() }}</span>
                                    <span class="text-[#94A3B8]">&bull;</span>
                                    <span class="text-amber-700 dark:text-amber-400" title="Partially Supported">~ {{ $p->claims->where('verdict', 'PARTIALLY_SUPPORTED')->count() }}</span>
                                    <span class="text-[#94A3B8]">&bull;</span>
                                    <span class="text-rose-700 dark:text-rose-400" title="Unsupported">✕ {{ $p->claims->where('verdict', 'UNSUPPORTED')->count() }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-[#64748B] dark:text-[#94A3B8] text-[11px]">
                                {{ $p->created_at->diffForHumans() }}
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('papers.show', $p) }}"
                                        class="px-3 py-1.5 rounded-lg bg-[#F8FAFC] dark:bg-[#0B1120] hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 text-blue-600 dark:text-blue-400 border border-[#E2E8F0] dark:border-[#1E293B] text-xs font-medium transition-colors">
                                        View Report &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-[#64748B] dark:text-[#94A3B8]">
                                <p class="text-sm">No papers have been submitted yet.</p>
                                <a href="{{ route('papers.create') }}" class="inline-block mt-3 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                    Submit your first research paper &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($papers->hasPages())
            <div class="p-4 border-t border-[#E2E8F0] dark:border-[#1E293B]">
                {{ $papers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection


