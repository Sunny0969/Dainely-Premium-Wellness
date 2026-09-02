@extends('layouts.app')
@section('title', 'About Dainely â€" Keep Moving. Keep Doing What You Love.')
@section('meta_description', 'At Dainely, we create thoughtfully designed products, movement resources, and everyday wellness solutions to help adults stay comfortable and active.')

@section('content')

{{-- HERO SECTION --}}
<section class="relative overflow-hidden bg-gradient-to-br from-navy-950 to-navy-800 text-white py-24" aria-label="About hero">
  <div class="absolute inset-0 opacity-20">
    <img src="{{ asset('images/about-mission.png') }}" alt="" class="w-full h-full object-cover" aria-hidden="true">
  </div>
  <div class="absolute inset-0 bg-navy-950/70"></div>
  <div class="container-narrow relative z-10 text-center px-4">
    <p class="eyebrow text-gold-400 mb-4">About Dainely</p>
    <h1 class="font-display font-bold text-white mb-6" style="font-size:clamp(2.5rem,5vw,4rem);line-height:1.1">Keep Moving. Keep Doing What You Love.</h1>
    <p style="color: #e2e8f0;" class="text-lg leading-relaxed max-w-3xl mx-auto mb-6">
      At Dainely, we believe an active life is built around the things that matter most to you.<br>
      A morning walk. A round of golf. A game of pickleball. A weekend project. A day of travel. Time with family and friends.
    </p>
    <p style="color: #e2e8f0;" class="text-lg leading-relaxed max-w-3xl mx-auto mb-8">
      Getting older may change the way you moveâ€"but it doesn't have to change what you enjoy. Dainely creates thoughtfully designed products, movement resources, and everyday wellness solutions to help adults stay comfortable, supported, and engaged in the activities they love.
    </p>
    <div class="inline-block bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 mt-4">
        <p class="text-white text-xl font-bold">Our goal is simple: help you keep moving forward.</p>
    </div>
  </div>
</section>

{{-- WELLNESS DESIGNED FOR REAL LIFE (CARDS) --}}
<section class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-16 max-w-3xl mx-auto">
      <h2 class="font-display font-bold text-3xl lg:text-4xl text-navy-900 mb-6">Wellness Designed for Real Life</h2>
      <p class="text-lg text-slate-600">We don't believe wellness needs to be complicated. The best products are the ones that fit naturally into your everyday routineâ€"comfortable, practical, easy to understand, and designed with real life in mind.</p>
      <p class="text-lg text-slate-600 font-semibold mt-4">That's why Dainely brings together:</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Card 1 -->
      <div class="bg-white rounded-3xl p-8" style="padding: 2.5rem; shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-2xl bg-gold-600/10 text-gold-600 flex items-center justify-center mb-6">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="font-bold text-xl text-navy-900 mb-3">Everyday Support</h3>
        <p class="text-slate-500 leading-relaxed">Thoughtfully designed products that can become part of your daily routine.</p>
      </div>
      <!-- Card 2 -->
      <div class="bg-white rounded-3xl p-8" style="padding: 2.5rem; shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-2xl bg-gold-600/10 text-gold-600 flex items-center justify-center mb-6">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <h3 class="font-bold text-xl text-navy-900 mb-3">Movement & Mobility</h3>
        <p class="text-slate-500 leading-relaxed">Practical education and simple movement ideas to encourage an active lifestyle.</p>
      </div>
      <!-- Card 3 -->
      <div class="bg-white rounded-3xl p-8" style="padding: 2.5rem; shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-2xl bg-gold-600/10 text-gold-600 flex items-center justify-center mb-6">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
        </div>
        <h3 class="font-bold text-xl text-navy-900 mb-3">Comfort & Recovery</h3>
        <p class="text-slate-500 leading-relaxed">Products and routines designed to help you make comfort and recovery part of your day.</p>
      </div>
      <!-- Card 4 -->
      <div class="bg-white rounded-3xl p-8" style="padding: 2.5rem; shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-2xl bg-gold-600/10 text-gold-600 flex items-center justify-center mb-6">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
        </div>
        <h3 class="font-bold text-xl text-navy-900 mb-3">Active Living</h3>
        <p class="text-slate-500 leading-relaxed">Ideas and solutions that fit the way people actually live, work, travel, and enjoy their time.</p>
      </div>
    </div>
  </div>
</section>

{{-- OUR APPROACH (TWO COLUMNS) --}}
<section class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <h2 class="font-display font-bold text-3xl lg:text-4xl text-navy-900 mb-6">Our Approach</h2>
        <p class="text-lg text-slate-600 leading-relaxed mb-8">We believe feeling your best isn't about chasing quick fixes. It's about the everyday choices that add up over time. These principles guide how we think about our products, our educational content, and the Dainely experience.</p>
        
        <div class="space-y-6">
          <div class="flex gap-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-navy-100 text-navy-700 flex items-center justify-center font-bold">1</div>
            <div>
              <h4 class="font-bold text-navy-900 text-lg">Move</h4>
              <p class="text-slate-500">Keep movement as a regular part of your day.</p>
            </div>
          </div>
          <div class="flex gap-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-navy-100 text-navy-700 flex items-center justify-center font-bold">2</div>
            <div>
              <h4 class="font-bold text-navy-900 text-lg">Support</h4>
              <p class="text-slate-500">Choose practical products that fit your activities and personal routine.</p>
            </div>
          </div>
          <div class="flex gap-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-navy-100 text-navy-700 flex items-center justify-center font-bold">3</div>
            <div>
              <h4 class="font-bold text-navy-900 text-lg">Recover</h4>
              <p class="text-slate-500">Make time for rest, comfort, and simple recovery habits.</p>
            </div>
          </div>
          <div class="flex gap-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-navy-100 text-navy-700 flex items-center justify-center font-bold">4</div>
            <div>
              <h4 class="font-bold text-navy-900 text-lg">Stay Engaged</h4>
              <p class="text-slate-500">Keep doing the activities, hobbies, and experiences that make life meaningful.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400 rounded-3xl translate-x-4 translate-y-4 -z-10 opacity-30"></div>
        <img src="{{ asset('images/about-team.jpg') }}" alt="Dainely Approach" class="relative z-10 rounded-3xl shadow-lg w-full object-cover h-[500px]" onerror="this.src='https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
      </div>
    </div>
  </div>
</section>

{{-- WHY WE CREATED DAINELY (COLORED BOX) --}}
<section class="py-20 bg-navy-900 text-white">
  <div class="max-w-4xl mx-auto px-6 text-center">
    <svg class="w-12 h-12 text-gold-400 mx-auto mb-6 opacity-50" fill="currentColor" viewBox="0 0 32 32"><path d="M10 8c-3.3 0-6 2.7-6 6v10h10V14H8.6c.8-2.3 3-4 5.4-4V8zm16 0c-3.3 0-6 2.7-6 6v10h10V14h-5.4c.8-2.3 3-4 5.4-4V8z"></path></svg>
    <h2 class="font-display font-bold text-3xl lg:text-4xl mb-8">Why We Created Dainely</h2>
    <p class="text-xl lg:text-2xl font-light leading-relaxed mb-10 text-navy-100">
      "People don't want to be defined by what limits them. They want to keep doing what they enjoy."
    </p>
    <div class="text-left text-navy-200 space-y-4 max-w-3xl mx-auto text-lg">
      <p>We saw an opportunity to create products that feel less like medical equipment and more like practical companions for everyday life.</p>
      <p>That means paying attention to the details that matterâ€"comfort, fit, usability, materials, design, and how a product actually fits into someone's routine. It also means listening. Customer feedback helps us understand what works, what doesn't, and where we can improve.</p>
      <p class="font-bold text-white pt-4">We don't believe in exaggerated promises. We believe in thoughtful design, useful information, honest communication, and continually making our products better.</p>
    </div>
  </div>
</section>

{{-- BUILT AROUND PEOPLE & MORE THAN PRODUCTS --}}
<section class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-8">
      
      <!-- Box 1 -->
      <div class="bg-white rounded-3xl p-8" style="padding: 3rem; shadow-sm border border-slate-100">
        <h2 class="font-display font-bold text-3xl text-navy-900 mb-6">Built Around People, Not Conditions</h2>
        <p class="text-slate-600 mb-4 leading-relaxed">Dainely is designed for peopleâ€"not diagnoses. Our customers have different lifestyles, different routines, and different reasons for wanting everyday support.</p>
        <p class="text-slate-600 mb-6 leading-relaxed">Some want to stay active on the golf course. Some enjoy pickleball. Some spend their days working, traveling, gardening, walking, or taking care of a home and family. Others simply want to make everyday movement and comfort a little easier.</p>
        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
          <p class="font-bold text-navy-900 text-lg mb-2">There isn't one definition of an active life.</p>
          <p class="text-gold-600 font-bold text-xl mb-3">There is only yours.</p>
          <p class="text-slate-600 text-sm">That's why we focus on practical solutions that can fit into the life you already live.</p>
        </div>
      </div>

      <!-- Box 2 -->
      <div class="bg-white rounded-3xl p-8" style="padding: 3rem; shadow-sm border border-slate-100">
        <h2 class="font-display font-bold text-3xl text-navy-900 mb-6">More Than Products</h2>
        <p class="text-slate-600 mb-6 leading-relaxed">Dainely is growing into more than a product brand. We are building a practical wellness platform around movement, comfort, mobility, and active living. That includes:</p>
        <ul class="space-y-4 mb-8">
          <li class="flex items-start gap-3">
            <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-slate-700">Thoughtfully designed support and comfort products</span>
          </li>
          <li class="flex items-start gap-3">
            <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-slate-700">Movement and mobility education</span>
          </li>
          <li class="flex items-start gap-3">
            <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-slate-700">Simple routines and practical tips</span>
          </li>
          <li class="flex items-start gap-3">
            <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-slate-700">Resources for active adults</span>
          </li>
          <li class="flex items-start gap-3">
            <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-slate-700">Ongoing product improvements informed by customer feedback</span>
          </li>
        </ul>
        <p class="text-slate-600 italic">Our products are only one part of the picture. We want to give people useful tools and information they can incorporate into everyday life.</p>
      </div>

    </div>
  </div>
</section>

{{-- FOUNDER PROFILE --}}
<section class="py-20 bg-white">
  <div class="max-w-5xl mx-auto px-6 lg:px-8">
    <div class="bg-navy-50 rounded-3xl p-8 lg:p-12 border border-navy-100 flex flex-col md:flex-row gap-10 items-center">
      <div class="w-48 h-48 rounded-full bg-navy-200 flex-shrink-0 border-4 border-white shadow-md overflow-hidden flex items-center justify-center">
        <!-- Placeholder for Founder Image -->
        <svg class="w-24 h-24 text-navy-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
      </div>
      <div>
        <h2 class="font-display font-bold text-3xl text-navy-900 mb-2">Vijay Reddy</h2>
        <p class="text-gold-600 font-bold uppercase tracking-wider text-sm mb-6">Founder & CEO</p>
        <p class="text-slate-600 mb-4 leading-relaxed">Dainely is led by Vijay Reddy, an entrepreneur and healthcare technology professional with more than 30 years of experience across technology, healthcare, and business.</p>
        <p class="text-slate-600 mb-4 leading-relaxed">Throughout his career, Vijay has focused on using technology and practical innovation to solve real-world problems. That experience continues to influence Dainely's approach today: understand what people actually need, build practical solutions, and keep improving based on real-world experience.</p>
        <p class="text-navy-900 font-semibold italic">"Vijay's vision for Dainely is to build a trusted wellness brand that helps people stay active, comfortable, and engaged with the lives they enjoy."</p>
      </div>
    </div>
  </div>
</section>

{{-- WHAT WE STAND FOR (GRID) --}}
<section class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-16">
      <h2 class="font-display font-bold text-3xl lg:text-4xl text-navy-900">What We Stand For</h2>
    </div>
    
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white p-8" style="padding: 2.5rem; rounded-3xl shadow-sm border border-slate-100">
        <h3 class="font-bold text-xl text-navy-900 mb-3 flex items-center gap-2">
          <span class="w-8 h-8 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center">1</span>
          Thoughtful Design
        </h3>
        <p class="text-slate-600">We pay attention to the details that influence comfort, usability, fit, and everyday experience.</p>
      </div>
      
      <div class="bg-white p-8" style="padding: 2.5rem; rounded-3xl shadow-sm border border-slate-100">
        <h3 class="font-bold text-xl text-navy-900 mb-3 flex items-center gap-2">
          <span class="w-8 h-8 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center">2</span>
          Practical Innovation
        </h3>
        <p class="text-slate-600">Innovation doesn't have to be complicated. We look for useful ways to make everyday products and routines better.</p>
      </div>
      
      <div class="bg-white p-8" style="padding: 2.5rem; rounded-3xl shadow-sm border border-slate-100">
        <h3 class="font-bold text-xl text-navy-900 mb-3 flex items-center gap-2">
          <span class="w-8 h-8 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center">3</span>
          Honest Communication
        </h3>
        <p class="text-slate-600">We believe customers deserve clear informationâ€"not exaggerated promises.</p>
      </div>
      
      <div class="bg-white p-8" style="padding: 2.5rem; rounded-3xl shadow-sm border border-slate-100">
        <h3 class="font-bold text-xl text-navy-900 mb-3 flex items-center gap-2">
          <span class="w-8 h-8 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center">4</span>
          Continuous Improvement
        </h3>
        <p class="text-slate-600">We listen to feedback and use what we learn to improve our products and customer experience.</p>
      </div>
      
      <div class="bg-white p-8" style="padding: 2.5rem; rounded-3xl shadow-sm border border-slate-100">
        <h3 class="font-bold text-xl text-navy-900 mb-3 flex items-center gap-2">
          <span class="w-8 h-8 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center">5</span>
          Everyday Wellness
        </h3>
        <p class="text-slate-600">Wellness is not a single event or destination. It is built through the small choices and habits that become part of everyday life.</p>
      </div>
    </div>
  </div>
</section>

{{-- CTA SECTION --}}
<section class="py-20 bg-white">
  <div class="max-w-5xl mx-auto px-6 lg:px-8">
    <div class="bg-navy-900 rounded-[2.5rem] p-8 text-center shadow-xl relative overflow-hidden" style="padding: 3rem 2rem;">
      <!-- Decorative background -->
      <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-gold-500 rounded-full blur-3xl opacity-20 pointer-events-none"></div>
      <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-blue-500 rounded-full blur-3xl opacity-20 pointer-events-none"></div>
      
      <div class="relative z-10">
        <p class="text-gold-400 font-bold tracking-wider uppercase text-sm mb-4">Our Commitment to You</p>
        <h2 class="text-3xl md:text-5xl font-display font-bold text-white mb-6">Keep Moving. Keep Doing What You Love.</h2>
        <p style="color: #e2e8f0;" class="text-lg max-w-3xl mx-auto mb-6">
          We are committed to creating products and resources that are practical, thoughtfully designed, and easy to incorporate into everyday routines. We will continue listening to our customers, learning from their experiences, and looking for better ways to serve them.
        </p>
        <p class="text-white text-xl font-bold mb-10 italic">
          "Because we don't think staying active is about doing everything perfectly. It's about finding ways to keep moving."
        </p>
        
        <p style="color: #cbd5e1;" class="mb-8">Your life is already full of things worth staying active for. Dainely is here to support the journey.</p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="btn-primary bg-gold-500 hover:bg-gold-400 text-navy-900 border-none px-8 py-4 text-lg font-bold rounded-xl transition-transform hover:-translate-y-1">Explore Dainely Products</a>
          <a href="{{ route('education.index', ['locale' => app()->getLocale()]) }}" class="btn-secondary bg-white/10 hover:bg-white/20 text-white border-white/20 px-8 py-4 text-lg font-bold rounded-xl transition-all">Explore Movement & Mobility</a>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
