@extends('layouts.app')
@section('title', ($article['meta_title'] ?? $article['title']) . ' | Dainely')
@section('meta_description', $article['meta_description'] ?? $article['excerpt'] ?? '')
@section('content')

<style>
  /* Override Tailwind Typography max-width restriction (65ch) completely */
  .prose, .prose-lg, .blog-rich-content,
  .prose *, .blog-rich-content * {
      max-width: none !important;
  }
  
  /* Ensure images stay inside the content column and look good */
  .blog-rich-content img {
      max-width: 100% !important;
      height: auto !important;
      border-radius: 1rem !important; /* Soft corners for images */
      margin-top: 2rem !important;
      margin-bottom: 2rem !important;
  }
  
  /* Fix Table of Contents Anchor Link Offset & Wrapping Behavior */
  .blog-rich-content h1, .blog-rich-content h2, .blog-rich-content h3, .blog-rich-content h4 {
      scroll-margin-top: 140px;
      text-wrap: wrap !important; /* Disables Tailwind's text-wrap: balance which cuts lines early */
      white-space: normal !important;
      word-break: break-word !important;
      width: 100% !important;
  }

  /* Match Admin Panel (Quill Editor) Spacing exactly */
  .blog-rich-content p, 
  .blog-rich-content ul, 
  .blog-rich-content ol,
  .blog-rich-content table {
      margin-top: 0 !important;
      margin-bottom: 1rem !important;
      line-height: 1.6 !important;
  }
  .blog-rich-content li {
      margin-top: 0 !important;
      margin-bottom: 0.25rem !important; /* Single spacing for list items */
  }
  .blog-rich-content h2 {
      margin-top: 2rem !important;
      margin-bottom: 1rem !important;
      line-height: 1.3 !important;
  }
  .blog-rich-content h3, .blog-rich-content h4 {
      margin-top: 1.5rem !important;
      margin-bottom: 0.75rem !important;
  }
  /* Allow empty paragraphs to create exact double spacing if user presses Enter multiple times */
  .blog-rich-content p:empty::before {
      content: "\00a0";
  }

  /* Force Tables to have full borders matching the admin editor */
  .blog-rich-content table {
      border-collapse: collapse !important;
      width: 100% !important;
      border: 1px solid #cbd5e1 !important; /* slate-300 */
      margin-top: 1.5rem !important;
      margin-bottom: 1.5rem !important;
  }
  .blog-rich-content th, 
  .blog-rich-content td {
      border: 1px solid #cbd5e1 !important;
      padding: 0.75rem 1rem !important;
      vertical-align: top;
  }
  .blog-rich-content th {
      background-color: #f8fafc !important; /* slate-50 */
      font-weight: 700 !important;
  }

  /* Robust desktop 2-column sidebar layout that works without Tailwind JIT Jars compiled */
  @media (min-width: 1024px) {
      .blog-layout-grid {
          display: grid !important;
          grid-template-columns: 1fr 300px !important; /* Content left, Sidebar right */
          gap: 4rem !important;
      }
  }
</style>

<section class="bg-white min-h-screen">
  {{-- Outer Container with Double Margins (Narrower width for readability) --}}
  <div class="max-w-5xl mx-auto px-6 lg:px-10 py-10 lg:py-14">
    
    {{-- 1. Back Link --}}
    <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-slate-500 hover:text-navy-700 font-medium text-sm mb-8 transition-colors">
      <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Back to all blogs
    </a>

    <div class="blog-layout-grid">
      
      {{-- Main Content Column --}}
      <div class="min-w-0">
        
        {{-- 2. Cover Image --}}
        <div class="rounded-3xl overflow-hidden bg-slate-50 mb-10 border border-slate-100 flex items-center justify-center">
          <img src="{{ asset('images/' . $article['image']) }}" alt="{{ $article['image_alt'] }}" fetchpriority="high" loading="eager" class="w-full h-auto block" style="max-height: 600px; object-fit: cover;">
        </div>

        {{-- 3. Title --}}
        <h1 class="font-display font-bold text-3xl lg:text-4xl text-slate-900 leading-tight mb-6" style="text-wrap: wrap !important;">{{ $article['title'] }}</h1>

        {{-- 4. Date and Author Meta --}}
        <div class="flex flex-wrap items-center gap-4 text-slate-500 text-sm mb-10 pb-8 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center border border-slate-200">
              <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
            </div>
            <span class="font-medium text-slate-700">{{ $article['author'] }}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
            <span>{{ $article['date'] }}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
            <span>{{ $article['readtime'] }}</span>
          </div>
          <span class="inline-block bg-navy-50 text-navy-700 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full">{{ $article['category'] }}</span>
        </div>

        {{-- 5. Main Content --}}
        <article class="prose prose-slate max-w-none" style="max-width: 100% !important; width: 100% !important;">
          <p class="lead text-xl text-slate-600 leading-relaxed mb-8" style="max-width: 100% !important;">{{ $article['excerpt'] }}</p>
          <div class="blog-rich-content" style="max-width: 100% !important; width: 100% !important;">
            {!! str_replace('<iframe ', '<iframe referrerpolicy="strict-origin-when-cross-origin" ', $article['content'] ?? '') !!}
          </div>

          @if(!empty($article['tags']))
            @php
              $tagsArray = is_string($article['tags']) ? array_filter(array_map('trim', explode(',', $article['tags']))) : (is_array($article['tags']) ? $article['tags'] : []);
            @endphp
            @if(!empty($tagsArray))
              <div class="mt-10 pt-8 border-t border-slate-100 flex flex-wrap gap-2 items-center mb-10">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider mr-2">Tags:</span>
                @foreach($tagsArray as $tag)
                  <span class="px-3 py-1 bg-slate-50 text-slate-600 rounded-full text-xs font-medium border border-slate-200/50">#{{ $tag }}</span>
                @endforeach
              </div>
            @endif
          @endif
        </article>

        {{-- FAQs Section --}}
        @if(!empty($article['faqs']))
          @php
            $faqsArray = is_string($article['faqs']) ? json_decode($article['faqs'], true) : $article['faqs'];
            $faqsArray = is_array($faqsArray) ? $faqsArray : [];
          @endphp
          @if(!empty($faqsArray))
            <div class="pt-10 border-t border-slate-200 mt-10">
              <h2 class="text-2xl font-bold text-slate-800 mb-6">Frequently Asked Questions</h2>
              <div class="space-y-4" x-data="{ activeFaq: null }">
                @foreach($faqsArray as $index => $faq)
                  <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50/50">
                    <button type="button" class="w-full flex justify-between items-center px-6 py-4 text-left font-bold text-slate-800 hover:bg-slate-100 transition-colors text-base" @click="activeFaq === {{ $index }} ? activeFaq = null : activeFaq = {{ $index }}">
                      <span>{{ $faq['question'] ?? '' }}</span>
                      <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="activeFaq === {{ $index }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="px-6 py-4 bg-white border-t border-slate-150 text-slate-600 text-sm leading-relaxed" x-show="activeFaq === {{ $index }} ? true : false" x-cloak>
                      {!! nl2br(e($faq['answer'] ?? '')) !!}
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif
        @endif

        {{-- Bottom Recommendations and Related articles --}}
        <div class="grid md:grid-cols-2 gap-8 mt-12">
          <div>
            @include('partials.shopify-product-sidebar', [
              'product' => $featuredShopifyProduct ?? null,
              'heading' => 'Recommended',
              'description' => 'Shop our recommended wellness product from the live catalog.',
            ])
          </div>
          <div>
            @if(!empty($related))
            <div class="card p-6 bg-slate-50 border-slate-100 rounded-2xl">
              <h3 class="font-semibold text-navy-900 mb-4">Related Articles</h3>
              <div class="space-y-4">
                @foreach($related as $rel)
                <a href="{{ route('blog.show', ['locale' => app()->getLocale(), 'slug' => $rel['slug']]) }}" class="flex gap-4 group items-center">
                  <img src="{{ asset('images/' . $rel['image']) }}" alt="{{ $rel['title'] }}" class="w-20 h-20 object-cover rounded-xl flex-shrink-0">
                  <div>
                    <p class="text-sm font-medium text-slate-800 group-hover:text-navy-700 transition-colors leading-snug">{{ $rel['title'] }}</p>
                    <p class="text-xs text-slate-400 mt-1.5">{{ $rel['readtime'] }}</p>
                  </div>
                </a>
                @endforeach
              </div>
            </div>
            @endif
          </div>
        </div>

      </div>

      {{-- 6. Table of Contents (Right Sidebar) --}}
      <aside class="hidden lg:block">
        <div class="sticky top-24 self-start bg-white p-6 rounded-2xl border border-slate-200 shadow-sm w-full">
          <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-5">On This Page</h3>
          <nav>
            <ul id="toc-list" class="space-y-3.5 text-sm text-slate-600">
              <!-- Dynamically generated -->
            </ul>
          </nav>
        </div>
      </aside>

    </div>
  </div>
</section>

@include('components.related-content', [
  'title' => __('Related Resources'),
  'links' => $relatedLinks ?? [],
])

<script>
document.addEventListener('DOMContentLoaded', function () {
    const contentArea = document.querySelector('.blog-rich-content');
    const tocList = document.getElementById('toc-list');
    
    if (contentArea && tocList) {
        const headings = contentArea.querySelectorAll('h2');
        if (headings.length === 0) {
            const tocContainer = tocList.closest('aside');
            if (tocContainer) {
                tocContainer.style.display = 'none';
                const mainGrid = document.querySelector('.blog-layout-grid');
                if (mainGrid) {
                    mainGrid.style.display = 'block';
                }
            }
        } else {
            // Setup Intersection Observer for active state
            const observerOptions = {
                root: null,
                rootMargin: '-140px 0px -70% 0px', // Triggers when heading passes the top nav
                threshold: 0
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Remove active state from all links
                        document.querySelectorAll('.toc-link').forEach(link => {
                            link.classList.remove('font-bold', 'text-navy-700');
                            link.classList.add('text-slate-500');
                            
                            const icon = link.querySelector('svg');
                            if(icon) {
                                icon.classList.remove('text-navy-600');
                                icon.classList.add('text-slate-300');
                            }
                        });
                        
                        // Add active state to the intersecting link
                        const activeLink = document.querySelector(`.toc-link[href="#${entry.target.id}"]`);
                        if (activeLink) {
                            activeLink.classList.remove('text-slate-500');
                            activeLink.classList.add('font-bold', 'text-navy-700');
                            
                            const activeIcon = activeLink.querySelector('svg');
                            if(activeIcon) {
                                activeIcon.classList.remove('text-slate-300');
                                activeIcon.classList.add('text-navy-600');
                            }
                        }
                    }
                });
            }, observerOptions);

            // Create links and start observing
            headings.forEach((heading, idx) => {
                const headingText = heading.textContent.replace(/\u00a0/g, ' ').trim();
                if (!headingText) return; // Skip empty headings (e.g. <h2><br></h2>)
                
                if (!heading.id) {
                    heading.id = 'heading-' + idx;
                }
                
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = '#' + heading.id;
                a.className = 'toc-link group flex items-start gap-2.5 py-1.5 transition-colors text-slate-500 hover:text-navy-700';
                
                a.innerHTML = `
                    <svg class="w-3.5 h-3.5 mt-1 flex-shrink-0 text-slate-300 group-hover:text-navy-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="leading-snug transition-all">${headingText}</span>
                `;
                
                li.appendChild(a);
                tocList.appendChild(li);
                
                // Observe this heading
                observer.observe(heading);
                
                // Click handler for instant active state feedback
                a.addEventListener('click', () => {
                    document.querySelectorAll('.toc-link').forEach(link => {
                        link.classList.remove('font-bold', 'text-navy-700');
                        link.classList.add('text-slate-500');
                        link.querySelector('svg')?.classList.replace('text-navy-600', 'text-slate-300');
                    });
                    a.classList.remove('text-slate-500');
                    a.classList.add('font-bold', 'text-navy-700');
                    a.querySelector('svg')?.classList.replace('text-slate-300', 'text-navy-600');
                });
            });
        }
    }
});
</script>

@endsection


