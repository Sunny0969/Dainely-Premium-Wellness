@extends('layouts.app')
@section('title', 'About Dainely — Our Mission, Science & Story')
@section('meta_description', 'Learn how Dainely was founded by wellness professionals to solve chronic back pain with medical-grade, clinically developed solutions trusted by 50,000+ customers.')
@section('og_image', asset('images/about-team.jpg'))

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-gradient-to-br from-navy-950 to-navy-800 text-white py-24" aria-label="About hero">
  <div class="absolute inset-0 opacity-20">
    <img src="{{ asset('images/about-mission.png') }}" alt="" class="w-full h-full object-cover" aria-hidden="true">
  </div>
  <div class="absolute inset-0 bg-navy-950/60"></div>
  <div class="container-narrow relative z-10 text-center">
    <p class="eyebrow text-gold-400 mb-4">Our Leadership</p>
    <h1 class="font-display font-bold text-white mb-6" style="font-size:clamp(2.5rem,5vw,4rem);line-height:1.1">Visionary Leadership and Expertise</h1>
    <p class="text-navy-200 text-lg leading-relaxed max-w-2xl mx-auto">At Dainely™, our mission to empower individuals to live pain-free and move confidently is driven by a passionate and dedicated team. While Vijay Reddy provides visionary leadership, our success is also fueled by the expertise and commitment of key individuals who contribute significantly to various aspects of our operations.</p>
  </div>
</section>
{{-- About us --}}
<section class="section bg-section-alt" aria-label="Products and customers">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">What We Do · Who We Serve</p>
      <h2 class="heading-section mb-4">Empowering Lives Through Pain Relief Innovation</h2>
      <p class="text-lead max-w-3xl mx-auto">At Dainely™, we are dedicated to empowering lives through innovative, holistic pain relief solutions. As a leading provider in the health and wellness space, our mission is to help you live pain-free and move with confidence — whether you're battling sciatica, lower back pain, or neck pain, our expertly designed products offer non-invasive, drug-free relief that enhances your quality of life.</p>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 lg:gap-8">

      {{-- Products column --}}
      <div class="bg-white rounded-3xl p-6 md:p-8 shadow-soft">
        <div class="flex items-center gap-4 mb-6 pb-5 border-b border-slate-200">
          <div class="w-12 h-12 rounded-2xl bg-gold-600 text-white flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
          </div>
          <div>
            <p class="eyebrow mb-1">Our Products</p>
            <h3 class="font-display font-bold text-navy-900 text-xl leading-tight">Engineered for Specific Discomforts</h3>
      <p class="text-body text-sm">We offer a diverse range of pain relief products, each engineered to address specific discomforts:</p>
          
          </div>
        </div>

        <div class="space-y-5">
          @foreach([
            [
              'title' => 'Sciatica Relief',
              'desc'  => 'Targeted solutions — including specialized lumbar cushions and supportive back braces — designed to alleviate sciatic nerve pain and promote proper posture.',
              'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/></svg>',
            ],
            [
              'title' => 'Lower Back Relief',
              'desc'  => 'Heat therapy devices, ergonomic lumbar supports, and innovative back braces that stabilize and soothe lower back pain for improved mobility.',
              'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>',
            ],
            [
              'title' => 'Neck Pain Relief',
              'desc'  => 'Cervical pillows, traction devices, and neck braces that promote proper alignment and ease tension in your neck and shoulders.',
              'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>',
            ],
            [
              'title' => 'General Pain Relief',
              'desc'  => 'Therapeutic aids including topical analgesics, herbal remedies, massage tools, and heating pads — all crafted to support your overall wellness.',
              'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/></svg>',
            ],
          ] as $item)
          <div class="flex gap-4">
            <div class="w-10 h-10 rounded-xl bg-gold-600/10 text-gold-600 flex items-center justify-center flex-shrink-0 mt-0.5">
              {!! $item['icon'] !!}
            </div>
            <div>
              <p class="font-display font-bold text-navy-900 text-base mb-1">{{ $item['title'] }}</p>
              <p class="text-slate-500 text-sm leading-relaxed">{{ $item['desc'] }}</p>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Customers column --}}
      <div class="bg-white rounded-3xl p-6 md:p-8 shadow-soft">
        <div class="flex items-center gap-4 mb-6 pb-5 border-b border-slate-200">
          <div class="w-12 h-12 rounded-2xl bg-navy-900 text-white flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <div>
            <p class="eyebrow mb-1">Our Customers</p>
            <h3 class="font-display font-bold text-navy-900 text-xl leading-tight">Trusted by People & Practices</h3>
            <p class="text-body text-sm">Dainely™ serves a wide range of customers who seek effective, natural, and reliable pain management solutions:</p>
          </div>
        </div>

        <div class="space-y-5">
          @foreach([
            [
              'title' => 'Individual Consumers',
              'desc'  => 'Whether dealing with chronic or acute pain, our customers choose Dainely™ for safe, non-invasive relief options that help them reclaim their lives.',
              'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            ],
            [
              'title' => 'Healthcare Professionals',
              'desc'  => 'Chiropractors, physical therapists, and massage therapists rely on our products to complement their treatments and provide their patients with lasting relief.',
              'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 11v3.5a3.5 3.5 0 1 1-7 0V3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3"/><path d="M8 15a6 6 0 0 0 12 0v-3"/><circle cx="20" cy="10" r="2"/></svg>',
            ],
            [
              'title' => 'Corporate Clients',
              'desc'  => 'Companies and organizations partner with us to enhance workplace wellness, providing ergonomic solutions that promote employee health and productivity.',
              'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v16"/><path d="M14 22V10a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v12"/><path d="M2 22h20"/><path d="M7 8h.01"/><path d="M7 12h.01"/><path d="M7 16h.01"/><path d="M17 12h.01"/><path d="M17 16h.01"/></svg>',
            ],
          ] as $item)
          <div class="flex gap-4">
            <div class="w-10 h-10 rounded-xl bg-navy-900/10 text-navy-900 flex items-center justify-center flex-shrink-0 mt-0.5">
              {!! $item['icon'] !!}
            </div>
            <div>
              <p class="font-display font-bold text-navy-900 text-base mb-1">{{ $item['title'] }}</p>
              <p class="text-slate-500 text-sm leading-relaxed">{{ $item['desc'] }}</p>
            </div>
          </div>
          @endforeach
        </div>
      </div>

    </div>

    <div class="max-w-3xl mx-auto text-center mt-12">
      <p class="text-slate-600 text-base leading-relaxed">At Dainely™, every product is crafted with care, combining cutting-edge design with proven functionality. We invite you to <span class="font-semibold text-navy-900">explore our range</span> and join us on the journey to a healthier, pain-free life.</p>
    </div>
  </div>
</section>
{{-- MISSION --}}
<section class="section bg-white" aria-label="Leadership">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-start">
      <div>

        <div class="bg-navy-50 rounded-3xl p-6 md:p-8 mb-6">
          <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 rounded-2xl bg-gold-600 flex items-center justify-center text-white font-display font-bold text-lg flex-shrink-0">
              VR
            </div>
            <div>
              <p class="font-display font-bold text-navy-900 text-xl leading-tight">Vijay Reddy</p>
              <p class="text-gold-600 font-semibold text-sm">Chief Executive Officer</p>
            </div>
          </div>

          <p class="text-slate-600 text-sm leading-relaxed mb-4">Vijay Reddy is a seasoned entrepreneur and healthcare technology veteran with over 30 years of experience. As CEO of Dainely, he leads the design and development of innovative healthcare products, including the Dainely™ Belt — a patent-pending belt that helps alleviate back and neck pain.</p>

          <p class="text-slate-600 text-sm leading-relaxed mb-4">Vijay has a proven track record of building and growing successful companies. He played a key role in developing OSI Systems, Inc., a healthcare revenue cycle technology company, and later acquired and expanded a medical billing company. He is also the founder of Synthium Health, a healthcare technology company that provides data-driven solutions for healthcare providers.</p>

          <p class="text-slate-600 text-sm leading-relaxed">Passionate about using technology to improve healthcare outcomes, Vijay is committed to delivering high-quality products and services that make a positive impact on people's lives.</p>
        </div>

        <div class="grid grid-cols-3 gap-3">
          @foreach([
            ['30+', 'Years Experience'],
            ['3', 'Healthcare Ventures'],
            ['1', 'Patent Pending'],
          ] as [$val, $label])
          <div class="bg-white border border-slate-200 rounded-2xl p-4 text-center">
            <p class="font-display font-bold text-2xl text-navy-900">{{ $val }}</p>
            <p class="text-slate-500 text-xs mt-1 leading-tight">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>

      <div class="relative lg:sticky lg:top-24">
        <div class="absolute -inset-4 bg-gold-400/10 blur-3xl rounded-3xl"></div>
        <img src="{{ asset('images/about-mission.png') }}" alt="Dainely wellness studio — clean modern clinical environment" class="relative z-10 w-full rounded-3xl shadow-strong" loading="lazy" width="640" height="480">
      </div>
    </div>
  </div>
</section>

{{-- TEAM --}}
<section class="section bg-section-alt" aria-label="Our team">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">The Team</p>
      <h2 class="heading-section mb-4">Our Dedicated Team</h2>
      <p class="text-lead max-w-2xl mx-auto">Behind every Dainely product is a passionate and skilled team committed to delivering exceptional care, innovation, and service. We are proud to work with a diverse group of professionals who bring deep expertise and a shared commitment to helping people live pain-free lives.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
      @foreach([
        [
          'title' => 'Panel of Health Experts',
          'desc'  => 'Our clinical advisory board includes licensed occupational therapists, physical therapists, and other wellness professionals who guide our product development and ensure therapeutic effectiveness.',
          'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/><path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/><circle cx="20" cy="10" r="2"/></svg>',
        ],
        [
          'title' => 'Marketing and Growth Team',
          'desc'  => 'A team of seasoned marketing managers, advertising strategists, and brand storytellers help bring the Dainely message to life across digital platforms.',
          'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>',
        ],
        [
          'title' => 'Customer Support Specialists',
          'desc'  => 'Trained support agents who provide empathetic, timely assistance to ensure our customers feel heard, helped, and valued.',
          'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H4a1 1 0 0 1-1-1v-6a9 9 0 0 1 18 0v6a1 1 0 0 1-1 1h-2a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>',
        ],
        [
          'title' => 'Product and Operations Team',
          'desc'  => 'From logistics experts to supply chain coordinators, our behind-the-scenes team ensures every belt is delivered on time and to the highest quality standards.',
          'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
        ],
        [
          'title' => 'Creative and Media Team',
          'desc'  => 'Our designers, content creators, and video editors help communicate our mission in visually compelling and emotionally resonant ways.',
          'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>',
        ],
        [
          'title' => 'Technology and Data Analysts',
          'desc'  => 'Supporting backend systems, data-driven insights, and platform performance to ensure a seamless customer experience.',
          'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M7 16V9"/><path d="M11 16V5"/><path d="M15 16v-6"/><path d="M19 16v-3"/></svg>',
        ],
      ] as $team)
      <div class="card p-6 h-full hover:shadow-medium transition-shadow duration-200">
        <div class="w-12 h-12 rounded-2xl bg-gold-600/10 text-gold-600 flex items-center justify-center mb-4">
          {!! $team['icon'] !!}
        </div>
        <h3 class="font-display font-bold text-navy-900 text-base mb-2">{{ $team['title'] }}</h3>
        <p class="text-slate-500 text-sm leading-relaxed">{{ $team['desc'] }}</p>
      </div>
      @endforeach
    </div>

    <div class="max-w-3xl mx-auto text-center bg-white/60 rounded-3xl p-6 md:p-8">
      <p class="text-slate-600 text-base leading-relaxed">Each member of our team—whether full-time or contracted—plays a vital role in Dainely's mission to make <span class="font-semibold text-navy-900">clinically backed pain relief solutions</span> accessible, affordable, and effective for all.</p>
    </div>
  </div>
</section>
{{-- CTA --}}
<section class="section bg-white text-center" aria-label="About CTA">
  <div class="container-narrow">
    <h2 class="heading-section mb-4">Ready to Experience the Difference?</h2>
    <p class="text-lead mb-8">Join 50,000+ people who chose science-backed wellness over quick fixes.</p>
    <a href="#" class="btn-primary-lg">Shop Dainely Belt — $89</a>
    <p class="text-slate-400 text-sm mt-4">30-day money-back guarantee · Free shipping over $75</p>
  </div>
</section>

@endsection
