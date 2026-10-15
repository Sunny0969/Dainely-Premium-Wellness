@php
    $headerTranslations = [
        'fr' => [
            'meta_title' => 'Guides d\'Éducation Santé et Bien-être | Dainely',
            'meta_description' => 'Découvrez des moyens pratiques d\'améliorer le mouvement, la mobilité, la posture et la récupération avec des ressources éducatives conçues pour vous.',
            'hero_title' => 'Éducation et Ressources',
            'hero_subtitle' => 'Découvrez des guides et des routines conçus pour vous aider à bouger librement, réduire l\'inconfort et adopter un mode de vie actif.',
        ],
        'de' => [
            'meta_title' => 'Gesundheits- und Wellness-Bildungsratgeber | Dainely',
            'meta_description' => 'Lernen Sie praktische Möglichkeiten kennen, um Bewegung, Mobilität, Haltung und Erholung zu unterstützen.',
            'hero_title' => 'Bildung & Ressourcen',
            'hero_subtitle' => 'Entdecken Sie fachmännisch erstellte Leitfäden und Routinen, die Ihnen helfen, sich frei zu bewegen, Beschwerden zu lindern und einen aktiven Lebensstil zu führen.',
        ],
        'en' => [
            'meta_title' => 'Health and Wellness Education Guides | Dainely',
            'meta_description' => 'Learn practical ways to support movement, mobility, posture and recovery with educational resources designed to help you stay comfortable, active and well.',
            'hero_title' => 'Education & Resources',
            'hero_subtitle' => 'Discover expertly crafted guides and routines designed to help you move freely, reduce discomfort, and embrace an active lifestyle.',
        ]
    ];
    $ht = $headerTranslations[$locale] ?? $headerTranslations['en'];
@endphp

@extends('layouts.app')
@section('title', $ht['meta_title'])
@section('meta_description', $ht['meta_description'])

@section('content')

<section class="bg-navy-900 text-white py-20 lg:py-28 relative overflow-hidden">
    <div class="absolute inset-0 bg-grid-white/[0.05] bg-[length:32px_32px]"></div>
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 text-center">
        <h1 class="font-display font-bold text-4xl lg:text-6xl tracking-tight mb-6 text-white">{{ $ht['hero_title'] }}</h1>
        <p class="text-xl text-navy-200 max-w-2xl mx-auto leading-relaxed">{{ $ht['hero_subtitle'] }}</p>
    </div>
</section>

<section class="py-16 lg:py-24 bg-slate-50" x-data="{ activeCategory: 'All' }">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        
        @if(isset($breadcrumbs) && is_array($breadcrumbs))
            <div class="mb-10 text-sm text-slate-500 flex flex-wrap items-center gap-2">
                @foreach($breadcrumbs as $index => $crumb)
                    @if($crumb['url'])
                        <a href="{{ $crumb['url'] }}" class="hover:text-navy-600 transition-colors">{{ $crumb['name'] }}</a>
                    @else
                        <span class="text-slate-400 font-medium">{{ $crumb['name'] }}</span>
                    @endif
                    
                    @if(!$loop->last)
                        <svg class="w-3 h-3 text-slate-300 mx-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Filter Tags / Category Pills --}}
        @php
            $catTranslations = [
                'fr' => [
                    'all' => 'Tous les articles',
                    'Movement & Mobility' => 'Mouvement et Mobilité',
                    'Back & Core Support' => 'Soutien du Dos et du Tronc',
                    'Posture & Alignment' => 'Posture et Alignement',
                    'Recovery & Relaxation' => 'Récupération et Relaxation',
                    'Active 50+ Lifestyle' => 'Mode de Vie Actif 50+',
                    'Everyday Comfort' => 'Confort au Quotidien',
                    'read_guide' => 'Lire le guide',
                    '5_min_read' => 'Lecture 5 min',
                ],
                'de' => [
                    'all' => 'Alle Artikel',
                    'Movement & Mobility' => 'Bewegung & Mobilität',
                    'Back & Core Support' => 'Rücken- & Rumpfstütze',
                    'Posture & Alignment' => 'Haltung & Ausrichtung',
                    'Recovery & Relaxation' => 'Erholung & Entspannung',
                    'Active 50+ Lifestyle' => 'Aktiver Lebensstil 50+',
                    'Everyday Comfort' => 'Alltäglicher Komfort',
                    'read_guide' => 'Leitfaden lesen',
                    '5_min_read' => '5 Min. Lesezeit',
                ],
                'en' => [
                    'all' => 'All Articles',
                    'Movement & Mobility' => 'Movement & Mobility',
                    'Back & Core Support' => 'Back & Core Support',
                    'Posture & Alignment' => 'Posture & Alignment',
                    'Recovery & Relaxation' => 'Recovery & Relaxation',
                    'Active 50+ Lifestyle' => 'Active 50+ Lifestyle',
                    'Everyday Comfort' => 'Everyday Comfort',
                    'read_guide' => 'Read Guide',
                    '5_min_read' => '5 min read',
                ]
            ];
            $t = $catTranslations[$locale] ?? $catTranslations['en'];
            $defaultPlaceholder = 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=800&q=80';
        @endphp

        <div class="flex flex-wrap items-center gap-3 mb-12">
            <button 
                @click="activeCategory = 'All'"
                :class="activeCategory === 'All' ? 'bg-navy-600 text-white border-navy-600' : 'bg-white border-slate-200 text-slate-600 hover:border-navy-200 hover:text-navy-700'"
                class="px-4 py-2 rounded-full text-sm font-semibold shadow-sm transition-colors cursor-pointer border focus:outline-none">
                {{ $t['all'] }}
            </button>
            @php
                $allCategories = [
                    'Movement & Mobility',
                    'Back & Core Support',
                    'Posture & Alignment',
                    'Recovery & Relaxation',
                    'Active 50+ Lifestyle',
                    'Everyday Comfort'
                ];
            @endphp
            @foreach($allCategories as $cat)
                <button 
                    @click="activeCategory = '{{ addslashes($cat) }}'"
                    :class="activeCategory === '{{ addslashes($cat) }}' ? 'bg-navy-600 text-white border-navy-600' : 'bg-white border-slate-200 text-slate-600 hover:border-navy-200 hover:text-navy-700'"
                    class="px-4 py-2 rounded-full border text-sm font-semibold shadow-sm transition-colors cursor-pointer focus:outline-none">
                    {{ $t[$cat] ?? $cat }}
                </button>
            @endforeach
        </div>

        @forelse($groupedPages as $category => $pages)
            <div class="mb-16 last:mb-0" x-show="activeCategory === 'All' || activeCategory === '{{ addslashes($category) }}'" x-transition>
                <div class="flex items-center gap-4 mb-8">
                    <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy-900">{{ $t[$category] ?? $category }}</h2>
                    <div class="h-px bg-slate-200 flex-grow mt-1"></div>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pages as $page)
                        @php
                            $imgSrc = null;
                            if (!empty($page->hero_image)) {
                                if (\Illuminate\Support\Str::startsWith($page->hero_image, ['http://', 'https://', '//'])) {
                                    $imgSrc = $page->hero_image;
                                } elseif (\Illuminate\Support\Facades\Storage::disk('s3')->exists('education/' . $page->hero_image)) {
                                    $imgSrc = \Illuminate\Support\Facades\Storage::disk('s3')->url('education/' . $page->hero_image);
                                } elseif (file_exists(public_path('images/' . $page->hero_image))) {
                                    $imgSrc = asset('images/' . $page->hero_image);
                                } else {
                                    $imgSrc = $defaultPlaceholder;
                                }
                            } else {
                                $imgSrc = $defaultPlaceholder;
                            }
                            $plainDescription = trim(strip_tags($page->hero_description ?? ''));
                        @endphp
                        <a href="{{ route('education.show', ['locale' => $locale, 'slug' => $page->slug]) }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-navy-200 transition-all group flex flex-col h-full overflow-hidden">
                            <img src="{{ $imgSrc }}" alt="{{ $page->title }}" loading="lazy" class="w-full h-48 object-cover" onerror="this.onerror=null;this.src='{{ $defaultPlaceholder }}';">
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex-grow">
                                    <h3 class="font-bold text-lg text-slate-900 group-hover:text-navy-700 transition-colors mb-2 leading-tight">{{ $page->title }}</h3>
                                    @if(!empty($plainDescription))
                                        <p class="text-slate-500 text-sm line-clamp-2 leading-relaxed">{{ $plainDescription }}</p>
                                    @endif
                                </div>
                                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-400">
                                    <span>{{ $page->read_time ?: ($t['5_min_read'] ?? '5 min read') }}</span>
                                    <span class="text-navy-600 group-hover:translate-x-1 transition-transform flex items-center gap-1">{{ $t['read_guide'] ?? 'Read Guide' }} <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-20">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <h3 class="text-xl font-bold text-slate-700 mb-2">{{ $locale === 'fr' ? 'Aucune ressource trouvée' : ($locale === 'de' ? 'Keine Ressourcen gefunden' : 'No Resources Found') }}</h3>
                <p class="text-slate-500">{{ $locale === 'fr' ? 'Nous mettons actuellement à jour notre bibliothèque. Revenez bientôt.' : ($locale === 'de' ? 'Wir aktualisieren derzeit unsere Bibliothek. Schauen Sie bald wieder vorbei.' : 'We are currently updating our education library. Check back soon.') }}</p>
            </div>
        @endforelse

    </div>
</section>

@endsection
