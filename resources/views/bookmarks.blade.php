@extends(auth()->check() ? 'layouts.physio' : 'layouts.frontend')

@section('title', 'My Space')

@section('content')
<main class="{{ auth()->check() ? 'py-8' : 'bookmarks-page-wrapper' }}" style="{{ auth()->check() ? '' : 'padding-top: 140px; background: #F8FAFC; min-height: 100vh;' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Saved Content Dashboard Header (Canva style) -->
        <div class="mb-10 reveal-up">
            <h1 class="text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">Saved Content <span class="text-blue-600">Dashboard</span></h1>
            <p class="text-slate-500 font-medium text-lg">Search, filter, sort, open, and remove saved academic content from a single focused workspace.</p>
        </div>

        @auth
        <!-- Control Panel (Canva style) -->
        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 mb-12 flex flex-col lg:flex-row items-center gap-6 reveal-up" style="animation-delay: 0.1s;">
            <div class="relative flex-1 group w-full">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-300 group-focus-within:text-blue-600 transition-colors"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <input type="text" placeholder="Search bookmarked items..." class="block w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl text-base font-bold placeholder-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-50 transition-all">
            </div>
            <div class="flex items-center gap-4 w-full lg:w-auto">
                <div class="relative group flex-1 lg:w-48">
                    <select class="appearance-none block w-full pl-10 pr-10 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-600 focus:outline-none focus:ring-4 focus:ring-blue-50 cursor-pointer transition-all">
                        <option>All subjects</option>
                        <option>Anatomy</option>
                        <option>Orthopaedics</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-400"><path d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </div>
                </div>
                <div class="relative group flex-1 lg:w-40">
                    <select class="appearance-none block w-full pl-10 pr-10 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-600 focus:outline-none focus:ring-4 focus:ring-blue-50 cursor-pointer transition-all">
                        <option>Latest</option>
                        <option>Oldest</option>
                        <option>A-Z</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-400"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recently Saved (Canva style) -->
        <div class="mb-12 reveal-up" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-1">Recently Saved</h2>
                    <p class="text-slate-400 text-sm font-medium">Quick-access mini cards keep your newest study material within reach.</p>
                </div>
                <button class="w-12 h-12 rounded-2xl border border-slate-100 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:border-blue-200 transition-all">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Empty state for now since we don't have a real bookmark table yet -->
                <div class="col-span-full py-16 px-8 rounded-[2rem] border-2 border-dashed border-slate-200 bg-slate-50/50 text-center flex flex-col items-center">
                    <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-slate-300 mb-6">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Your space is quiet...</h3>
                    <p class="text-slate-500 max-w-xs mx-auto mb-6">Start browsing topics and click the star icon to save resources for later.</p>
                    <a href="{{ route('topics.index') }}" class="px-8 py-4 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition-all">Explore Materials</a>
                </div>
            </div>
        </div>
        @else
        <!-- Guest view for bookmarks (Canva-inspired clean modal) -->
        <div class="max-w-2xl mx-auto py-20 px-8 text-center bg-white rounded-[3rem] shadow-xl border border-slate-100 flex flex-col items-center">
             <div class="w-24 h-24 bg-blue-50 rounded-[2rem] flex items-center justify-center text-blue-600 mb-8 shadow-inner">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Sign in to sync your space</h2>
            <p class="text-slate-500 text-lg mb-10 leading-relaxed">Join thousands of students who save their study progress, clinical notes, and exam prep resources across devices.</p>
            <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                <a href="{{ route('login') }}" class="px-10 py-5 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">Sign In to Continue</a>
                <a href="{{ route('register') }}" class="px-10 py-5 bg-slate-50 text-slate-900 rounded-2xl font-bold hover:bg-slate-100 transition-all">Create Account</a>
            </div>
        </div>
        @endauth
    </div>
</main>

<style>
    .rounded-[2rem] { border-radius: 2rem; }
    .rounded-[3rem] { border-radius: 3rem; }
    .reveal-up { animation: revealUp 0.8s ease-out forwards; }
    @keyframes revealUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
