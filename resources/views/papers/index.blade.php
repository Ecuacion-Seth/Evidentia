@extends('layouts.app', ['title' => 'Academic Paper Submissions History'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Academic Submissions History</h1>
            <p class="text-sm text-slate-400 mt-1">Review evidence integrity reports, practical coverage ratios, and student revision audits.</p>
        </div>
        <a href="{{ route('papers.create') }}"
            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-semibold text-xs tracking-wide shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Paper Scan
        </a>
    </div>

    <!-- Table of Papers -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-800 font-bold">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Document Title</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Practical Coverage</th>
                        <th class="py-3.5 px-4">Audited Claims</th>
                        <th class="py-3.5 px-4">Submitted</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($papers as $p)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-4 px-4 sm:px-6">
                                <a href="{{ route('papers.show', $p) }}" class="font-bold text-white hover:text-indigo-300 transition-colors block text-sm">
                                    {{ $p->title }}
                                </a>
                                <span class="text-[11px] text-slate-400">{{ $p->original_filename }} &bull; ID #{{ $p->id }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                    {{ $p->status === 'completed' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20' : ($p->status === 'failed' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/20' : 'bg-amber-500/15 text-amber-400 border border-amber-500/20 animate-pulse') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-emerald-400">
                                {{ number_format($p->practical_coverage, 1) }}% OA
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1.5 font-semibold text-[11px]">
                                    <span class="text-emerald-400" title="Supported">✓ {{ $p->claims->where('verdict', 'SUPPORTED')->count() }}</span>
                                    <span class="text-slate-500">&bull;</span>
                                    <span class="text-amber-400" title="Partially Supported">~ {{ $p->claims->where('verdict', 'PARTIALLY_SUPPORTED')->count() }}</span>
                                    <span class="text-slate-500">&bull;</span>
                                    <span class="text-rose-400" title="Unsupported">✕ {{ $p->claims->where('verdict', 'UNSUPPORTED')->count() }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-slate-400 text-[11px]">
                                {{ $p->created_at->diffForHumans() }}
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('papers.show', $p) }}"
                                        class="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white border border-indigo-500/30 text-xs font-semibold transition-all">
                                        View Report &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-slate-400">
                                <p class="text-sm">No papers have been submitted yet.</p>
                                <a href="{{ route('papers.create') }}" class="inline-block mt-3 text-xs text-indigo-400 hover:underline">
                                    Submit your first research paper &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($papers->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $papers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
