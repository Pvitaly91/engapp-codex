@extends('layouts.gramlyze-new')

@section('title', 'Gramlyze — English Teaching Platform')
@section('description', 'Comprehensive platform for English teachers to create tests, analyze performance, and collaborate effectively.')

@section('content')
<!-- Hero Section -->
<section class="py-12 md:py-20" data-animate>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
    <div class="space-y-6">
      <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-full bg-teal-50 dark:bg-teal-900/30 border border-teal-200 dark:border-teal-800">
        <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
        <span class="text-sm font-medium text-teal-900 dark:text-teal-100">Professional Platform for Educators</span>
      </div>
      
      <h1 class="font-display text-4xl md:text-6xl font-bold text-slate-900 dark:text-white leading-tight">
        Empower Your<br/>
        <span class="bg-gradient-to-r from-teal-600 to-cyan-600 bg-clip-text text-transparent">English Teaching</span>
      </h1>
      
      <p class="text-lg md:text-xl text-slate-600 dark:text-slate-300 leading-relaxed">
        Create comprehensive tests, analyze student performance with AI-powered insights, and collaborate with your teaching team — all in one place.
      </p>
      
      <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 rounded-lg hover:from-teal-700 hover:to-cyan-700 transition-all shadow-lg hover:shadow-xl">
          Browse Test Catalog
          <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </a>
        <a href="{{ route('grammar-test') }}" class="inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-lg hover:border-teal-600 dark:hover:border-teal-500 transition-all">
          Start Creating
          <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </a>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-6 pt-8">
        <div class="space-y-1">
          <div class="text-3xl font-bold text-slate-900 dark:text-white">7,500+</div>
          <div class="text-sm text-slate-600 dark:text-slate-400">Test Items</div>
        </div>
        <div class="space-y-1">
          <div class="text-3xl font-bold text-slate-900 dark:text-white">120+</div>
          <div class="text-sm text-slate-600 dark:text-slate-400">Categories</div>
        </div>
        <div class="space-y-1">
          <div class="text-3xl font-bold text-slate-900 dark:text-white">2,400+</div>
          <div class="text-sm text-slate-600 dark:text-slate-400">AI Reviews</div>
        </div>
      </div>
    </div>

    <div class="relative" data-animate>
      <div class="absolute inset-0 bg-gradient-to-tr from-teal-500/20 to-cyan-500/20 blur-3xl"></div>
      <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-8 border border-slate-200 dark:border-slate-700">
        <div class="space-y-6">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Quick Access</h3>
            <span class="px-3 py-1 text-xs font-medium text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/30 rounded-full">New</span>
          </div>
          
          <div class="space-y-3">
            <a href="{{ route('catalog-tests.cards') }}" class="block p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors group">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="p-2 bg-teal-100 dark:bg-teal-900/50 rounded-lg">
                    <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-slate-900 dark:text-white">Test Catalog</div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">Browse ready-made tests</div>
                  </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </a>

            <a href="{{ route('pages.index') }}" class="block p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors group">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="p-2 bg-cyan-100 dark:bg-cyan-900/50 rounded-lg">
                    <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13" />
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-slate-900 dark:text-white">Theory Pages</div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">Grammar & vocabulary notes</div>
                  </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </a>

            <a href="{{ route('question-review.index') }}" class="block p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors group">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-slate-900 dark:text-white">AI Reviews</div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">Automated answer analysis</div>
                  </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="py-16 md:py-24" data-animate>
  <div class="text-center mb-12">
    <h2 class="font-display text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4">
      Everything You Need to Teach English
    </h2>
    <p class="text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
      Powerful tools designed specifically for English educators and language schools
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    @php
    $features = [
      [
        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'title' => 'Test Builder',
        'description' => 'Create customized tests with our intuitive builder. Add questions, configure difficulty levels, and organize by topics.',
        'color' => 'teal'
      ],
      [
        'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
        'title' => 'AI Analysis',
        'description' => 'Get instant AI-powered insights on student answers. Identify patterns, common mistakes, and areas for improvement.',
        'color' => 'cyan'
      ],
      [
        'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'title' => 'Team Collaboration',
        'description' => 'Work together with your teaching team. Share resources, coordinate lessons, and track collective progress.',
        'color' => 'emerald'
      ]
    ];
    @endphp

    @foreach($features as $feature)
    <div class="group p-8 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-{{ $feature['color'] }}-500 dark:hover:border-{{ $feature['color'] }}-500 transition-all hover:shadow-lg">
      <div class="w-12 h-12 rounded-xl bg-{{ $feature['color'] }}-100 dark:bg-{{ $feature['color'] }}-900/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
        <svg class="w-6 h-6 text-{{ $feature['color'] }}-600 dark:text-{{ $feature['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}" />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">{{ $feature['title'] }}</h3>
      <p class="text-slate-600 dark:text-slate-400">{{ $feature['description'] }}</p>
    </div>
    @endforeach
  </div>
</section>

<!-- CTA Section -->
<section class="py-16 md:py-24" data-animate>
  <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-teal-600 to-cyan-600 p-12 md:p-16 text-white">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE0YzMuMzE0IDAgNiAyLjY4NiA2IDZzLTIuNjg2IDYtNiA2LTYtMi42ODYtNi02IDIuNjg2LTYgNi02ek0yNCA0NGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNnoiLz48L2c+PC9nPjwvc3ZnPg==')] opacity-30"></div>
    
    <div class="relative max-w-3xl mx-auto text-center">
      <h2 class="font-display text-3xl md:text-5xl font-bold mb-4">
        Ready to Transform Your Teaching?
      </h2>
      <p class="text-xl text-teal-50 mb-8">
        Join hundreds of educators who are already using Gramlyze to create better learning experiences.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-teal-600 bg-white rounded-lg hover:bg-teal-50 transition-all shadow-lg hover:shadow-xl">
          Explore Catalog
          <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </a>
        <a href="{{ route('login.show') }}" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white border-2 border-white/30 rounded-lg hover:bg-white/10 transition-all">
          Admin Access
          <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
          </svg>
        </a>
      </div>
    </div>
  </div>
</section>
@endsection
