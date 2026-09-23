@extends('layouts.app')

@section('title', $bundle->title . ' - Dainely')

@section('content')
<main class="bg-white" x-data="bundleCart({
    bundlePrice: {{ $bundle->price !== null ? $bundle->price : 'null' }},
    components: {{ json_encode($componentProducts) }}
})">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- Left: Images -->
            <div class="space-y-4">
                @php
                    $parsedImages = [];
                    $rawImages = $bundle->images;
                    if (is_string($rawImages)) {
                        $decoded = json_decode($rawImages, true);
                        if (is_array($decoded)) {
                            $rawImages = $decoded;
                        }
                    }
                    if (is_array($rawImages)) {
                        if (isset($rawImages['url'])) {
                            $parsedImages[] = $rawImages['url'];
                        } else {
                            foreach($rawImages as $img) {
                                if (is_string($img)) {
                                    $parsedImages[] = $img;
                                } elseif (is_array($img) && isset($img['url'])) {
                                    $parsedImages[] = $img['url'];
                                }
                            }
                        }
                    }
                @endphp
                @if(count($parsedImages) > 0)
                    <div class="aspect-square bg-slate-50 rounded-2xl overflow-hidden border border-slate-100">
                        <img src="{{ $parsedImages[0] }}" alt="{{ $bundle->title }}" class="w-full h-full object-cover">
                    </div>
                    @if(count($parsedImages) > 1)
                        <div class="grid grid-cols-4 gap-4">
                            @foreach(array_slice($parsedImages, 1, 4) as $img)
                                <div class="aspect-square bg-slate-50 rounded-xl overflow-hidden border border-slate-100 cursor-pointer hover:ring-2 ring-navy-600 transition">
                                    <img src="{{ $img }}" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="aspect-square bg-slate-100 rounded-2xl border border-slate-200 flex items-center justify-center">
                        <span class="text-slate-400">No images available</span>
                    </div>
                @endif
            </div>

            <!-- Right: Details & Configurator -->
            <div class="flex flex-col">
                <nav class="flex text-sm text-slate-500 mb-6 gap-2">
                    <a href="/" class="hover:text-navy-600">Home</a>
                    <span>/</span>
                    <a href="/bundles" class="hover:text-navy-600">Bundles</a>
                    <span>/</span>
                    <span class="text-slate-900 font-medium">{{ $bundle->title }}</span>
                </nav>

                <h1 class="text-4xl font-bold text-slate-900 mb-4">{{ $bundle->title }}</h1>
                
                <div class="text-2xl font-bold text-navy-700 mb-6">
                    $<span x-text="totalPrice"></span>
                </div>

                <div class="prose prose-slate prose-sm mb-8 text-slate-600 leading-relaxed">
                    {!! $bundle->description !!}
                </div>

                <form action="{{ route('bundle.add', ['locale' => $locale ?? 'en', 'bundleId' => $bundle->id]) }}" method="POST">
                    @csrf
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 mb-8 space-y-6">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Customize your components
                        </h3>

                        <template x-for="component in components" :key="component.db_product_id">
                            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                                <div class="flex items-start gap-4 mb-4">
                                    <div class="w-16 h-16 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 shrink-0">
                                        <template x-if="component.image">
                                            <img :src="component.image" class="w-full h-full object-cover">
                                        </template>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm" x-text="component.title"></h4>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Base value: $<span x-text="component.price"></span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Variants Selector -->
                                <template x-if="component.variants && component.variants.length > 1">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-2 uppercase tracking-wide">Select Option</label>
                                        <select 
                                            :name="'variants[' + component.db_product_id + ']'" 
                                            x-model="selectedVariants[component.db_product_id]"
                                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-navy-500 focus:ring-1 focus:ring-navy-500 bg-slate-50 hover:bg-white transition"
                                        >
                                            <template x-for="v in component.variants" :key="v.id">
                                                <option :value="v.id" x-text="v.title + ' (+$' + v.price + ')'"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                                <template x-if="!component.variants || component.variants.length <= 1">
                                    <div class="hidden">
                                        <!-- Hidden input for single variant products -->
                                        <input type="hidden" :name="'variants[' + component.db_product_id + ']'" :value="component.variants && component.variants.length > 0 ? component.variants[0].id : ''">
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="pt-6 border-t flex justify-between items-center">
                        <div>
                            <span class="block text-sm text-slate-500 font-medium">Total Bundle Price</span>
                            <span class="text-3xl font-bold text-navy-900" x-text="'$' + totalPrice"></span>
                        </div>
                        <button type="submit" class="bg-navy-900 hover:bg-navy-800 text-white font-bold text-lg px-8 py-4 rounded-xl transition shadow-lg shadow-navy-900/30">
                            Add to Cart
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
        
        {{-- Component Products Details Section --}}
        @if(count($componentProducts) > 0)
            <div class="mt-20 border-t border-slate-200 pt-16">
                <h2 class="text-3xl font-bold text-navy-900 text-center mb-12">What's Included in This Bundle</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach($componentProducts as $comp)
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col h-full">
                            <img src="{{ $comp['image'] }}" alt="{{ $comp['title'] }}" class="w-full aspect-square object-cover rounded-xl shadow-sm border border-slate-200 mb-6">
                            
                            <h3 class="text-2xl font-bold text-navy-900 mb-3">{{ $comp['title'] }}</h3>
                            
                            @if(!empty($comp['overview']) || !empty($comp['benefits_html']))
                                <div class="cms-richtext text-slate-600 text-sm sm:text-base leading-relaxed prose prose-slate max-w-none">
                                    @if(!empty($comp['overview']))
                                        {!! \App\Support\CmsHtml::normalize($comp['overview']) !!}
                                    @endif
                                    
                                    @if(!empty($comp['benefits_html']))
                                        <div class="mt-4">
                                            {!! \App\Support\CmsHtml::normalize($comp['benefits_html']) !!}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</main>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bundleCart', (config) => ({
        components: config.components,
        baseBundlePrice: config.bundlePrice,
        selectedVariants: {},

        init() {
            // Initialize with first variant for each component
            this.components.forEach(c => {
                if(c.variants && c.variants.length > 0) {
                    this.selectedVariants[c.db_product_id] = String(c.variants[0].id);
                }
            });
        },

        get totalPrice() {
            // Always return the fixed bundle price set by the admin in CMS.
            if (this.baseBundlePrice !== null && this.baseBundlePrice !== 'null') {
                return parseFloat(this.baseBundlePrice).toFixed(2);
            }
            
            // Fallback just in case price is 0
            let sum = 0;
            for (const [id, variantId] of Object.entries(this.selectedVariants)) {
                let comp = this.components.find(c => c.db_product_id == id);
                if (comp) {
                    let variant = comp.variants.find(v => String(v.id) === String(variantId));
                    let qty = comp.bundle_quantity || 1;
                    sum += parseFloat(variant?.price || 0) * qty;
                }
            }
            return sum.toFixed(2);
        }
    }))
})
</script>
@endsection

