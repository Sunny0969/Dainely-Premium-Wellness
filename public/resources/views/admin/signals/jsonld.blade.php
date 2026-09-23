@extends('layouts.admin')

@section('admin_title', 'JSON-LD Builder (Admin Only)')

@section('admin_content')
<div class="space-y-6" x-data="jsonLdBuilder()">
    
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-navy-900">Product Schemas</h2>
            <div class="space-x-3">
                <a href="/{{ $adminBase }}/signals" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Back to AI Signals
                </a>
            </div>
        </div>

        <p class="text-sm text-slate-600 mb-6">
            Fill these fields to generate proper JSON-LD structures for products. As requested, this is currently for admin management only and will not be displayed on the frontend yet.
        </p>

        <form action="/{{ $adminBase }}/signals/json-ld/publish" method="POST" id="schemaForm">
            @csrf
            
            <template x-for="(schema, index) in schemas" :key="index">
                <div class="bg-white rounded-lg border border-slate-200 mb-8 relative shadow-sm">
                    <h3 class="font-bold text-lg text-slate-800 p-6 border-b border-slate-200" x-text="schema.org_name ? schema.org_name : 'New Company Configuration'"></h3>
                    
                    <div class="p-6">
                        {{-- Organization & Website Entity --}}
                        <div class="bg-slate-50 p-5 rounded-lg border border-slate-200">
                            <h4 class="font-bold text-sm text-slate-700 mb-4 border-b border-slate-200 pb-2">Organization & Brand Settings (Company Level)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Organization Name</label>
                                    <input type="text" x-model="schema.org_name" :name="'schemas['+index+'][org_name]'" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-navy-500 focus:ring-1 focus:ring-navy-500" placeholder="e.g. Dainely">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Organization Logo URL</label>
                                    <input type="text" x-model="schema.org_logo" :name="'schemas['+index+'][org_logo]'" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-navy-500 focus:ring-1 focus:ring-navy-500" placeholder="e.g. https://dainely.com/images/logo.png">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Brand Name</label>
                                    <input type="text" x-model="schema.brand" :name="'schemas['+index+'][brand]'" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-navy-500 focus:ring-1 focus:ring-navy-500" placeholder="e.g. Dainely">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="p-4 border-t border-slate-200 bg-slate-50 rounded-b-lg flex justify-between items-center">
                        <button type="button" @click="removeSchema(index)" class="px-4 py-2 text-sm font-semibold text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-200">
                            Remove Configuration
                        </button>
                        <button type="button" @click="schema.showPreview = !schema.showPreview" class="px-4 py-2 text-sm font-semibold text-slate-700 hover:text-slate-900 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg transition-colors shadow-sm">
                            <span x-text="schema.showPreview ? 'Hide Preview' : 'Show JSON-LD Preview'"></span>
                        </button>
                    </div>

                    {{-- Preview Section --}}
                    <div x-show="schema.showPreview" class="p-5 bg-[#0f172a] rounded-b-lg border-t border-slate-700">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-mono text-slate-400">Preview (Live updating)</span>
                        </div>
                        <pre class="text-sm font-mono overflow-x-auto whitespace-pre-wrap p-4 bg-black/30 rounded-lg border border-slate-700" style="color: #4ade80;" x-text="JSON.stringify(generateJsonLd(schema), null, 2)"></pre>
                    </div>
                </div>
            </template>
            
            <div class="flex justify-between items-center mt-6 pt-6 border-t border-slate-200">
                <button type="button" @click="addSchema()" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-navy-600 bg-navy-50 hover:bg-navy-100 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Company Config
                </button>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-navy-600 hover:bg-navy-700 rounded-lg transition-colors shadow-sm">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('jsonLdBuilder', () => ({
        schemas: @json($schemas),
        
        init() {
            if (this.schemas.length === 0) {
                this.addSchema();
            }
            this.schemas = this.schemas.map(s => ({
                ...s,
                showPreview: false
            }));
        },

        addSchema() {
            if (this.schemas.length >= 1) {
                alert("Only one global company configuration is needed.");
                return;
            }
            this.schemas.push({
                org_name: 'Dainely',
                org_logo: 'https://dainely.com/images/Dainelycut.png',
                brand: 'Dainely',
                showPreview: false
            });
        },

        removeSchema(index) {
            if(confirm('Are you sure?')) {
                this.schemas.splice(index, 1);
            }
        },

        generateJsonLd(s) {
            const orgName = s.org_name || 'Dainely';
            const orgLogo = s.org_logo || 'https://dainely.com/images/Dainelycut.png';
            const brandName = s.brand || 'Dainely';
            
            return {
                "@context": "https://schema.org",
                "@graph": [
                    {
                        "@type": "Organization",
                        "@id": "https://dainely.com/#organization",
                        "name": orgName,
                        "url": "https://dainely.com",
                        "logo": orgLogo
                    },
                    {
                        "@type": "WebSite",
                        "@id": "https://dainely.com/#website",
                        "url": "https://dainely.com",
                        "name": orgName,
                        "publisher": { "@id": "https://dainely.com/#organization" }
                    },
                    {
                        "@type": "Brand",
                        "@id": "https://dainely.com/#brand",
                        "name": brandName
                    }
                ]
            };
        }
    }));
});
</script>
@endsection