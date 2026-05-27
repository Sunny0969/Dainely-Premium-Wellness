@extends('layouts.app')
@section('title', 'Refund Policy | Dainely')
@section('meta_description', 'Dainely 30-day money-back guarantee and refund policy. Full refund, no questions asked.')
@section('content')
<section class="bg-navy-900 text-white py-12">
  <div class="container-narrow text-center">
    <h1 class="font-display font-bold text-3xl text-white mb-2">Refund Policy</h1>
    <p class="text-navy-300">30-Day Money-Back Guarantee</p>
  </div>
</section>
<section class="section bg-white">
  <div class="container-narrow">

    <!-- Hero Banner -->
    <div class="bg-sage-50 border-2 border-sage-200 rounded-3xl p-8 text-center mb-12">
      <div class="w-16 h-16 bg-sage-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
      </div>
      <h2 class="font-display font-bold text-2xl text-sage-800 mb-3">60-Day Return Policy</h2>
      <p class="text-sage-700 text-lg">You have 60 days after receiving your item to request a return. The product may be tested for your specific condition, but it must come back in its original packaging with no visible damage.</p>
    </div>

    <div class="prose prose-slate max-w-none prose-a:text-blue-600 prose-a:underline hover:prose-a:text-blue-700">

      <h2>How to Request a Return</h2>
      <ol>
        <li>Email us at <a href="mailto:contact@dainelylab.com">contact@dainelylab.com</a> with your order number — we usually respond within 1 business day.</li>
        <li>We'll provide you with the return address. Please note that the customer is responsible for producing and paying for the return shipping label.</li>
        <li>Include a paper inside the return package containing your order number and the email address associated with the order.</li>
        <li>Once we receive the item, we'll inspect it within 2 business days. If it arrives in perfect condition, we will refund the amount owed.</li>
      </ol>

      <h2>Return Conditions</h2>
      <ul>
        <li>The return request must be made within 60 days of receiving the item.</li>
        <li>The product may be used (so you can see whether it works for your specific condition), but it must have no visible damage — no scratches, marks, etc.</li>
        <li>The item must be returned in its original packaging.</li>
        <li>A paper inside the package must contain your order number and the email associated with the order.</li>
      </ul>

      <h2>Return Shipping</h2>
      <p>While we deliver internationally free of charge, return shipping is the customer's responsibility. We will only provide the return address — we do not pay for shipping labels or any extra return charges.</p>
      <p>The customer is required to ensure that the return is delivered successfully. We strongly recommend paying for tracked shipping, as we are not responsible for lost packages. <strong>If the return is lost in transit, we will not be able to issue a refund</strong>, as the order was never returned to us.</p>

      <h2>Refunds</h2>
      <p>We will notify you once we've received and inspected your return, and let you know whether the refund was approved. If approved, you'll be automatically refunded to your original payment method.</p>
      <p>Please remember it can take up to <strong>7 business days</strong> for your bank or credit card company to process and post the refund. For security reasons, we are only able to refund the card that was used at checkout.</p>

      <h2>Damages and Issues</h2>
      <p>Please inspect your order on arrival and contact us immediately if the item is defective, damaged, or you received the wrong item, so that we can evaluate the issue and make it right.</p>

      <h2>Exchanges</h2>
      <p>The fastest way to ensure you get what you want is to return the item you have. Once the return is accepted, make a separate purchase for the new item.</p>

      <h2>Exceptions / Non-Returnable Items</h2>
      <p>Certain types of items cannot be returned, including custom products such as special orders, bulk orders, and personalized items. Please get in touch if you have questions or concerns about your specific item.</p>

      <h2>Cancellation Policy</h2>
      <p>Once an order has been placed and payment has been processed, you have the right to cancel at any time <strong>prior to shipment</strong>. To cancel, please contact us as soon as possible through our customer service channels.</p>
      <p>If the cancellation request is received before the order has been shipped, we will process the cancellation and issue a full refund. However, if the order has already been shipped, you will need to return the product in accordance with our return policy.</p>
      <p>We recommend requesting any changes or cancellation within <strong>24 hours</strong> of placing the order. Please note that any expedited shipping fees or other surcharges will not be refunded in the case of a cancellation.</p>

      <h2>Cancelled Orders After Shipment</h2>
      <p>If a customer places an order and subsequently decides to cancel it, a refund is not guaranteed, as we may have already shipped the order. In cases where the order has been shipped, the customer will need to wait until they receive the order and then initiate the standard return process to be eligible for a refund.</p>

      <h2>Dispatched Orders Held by Shipping Company</h2>
      <p>All our orders are dispatched immediately. In some cases, after dispatch, an order may be held by the shipping company until we provide them with additional customer information. This may be necessary due to shipping regulations in the customer's country.</p>
      <p>If we require further details from you and you refuse to provide them, delivery will not be possible. If you request a refund under these circumstances — and your order has already been dispatched but is currently on hold — we are regretfully unable to refund the full cost of the product. In such cases, we will deduct the shipping fees that we incurred to send the order from our warehouse from your refund. While we usually cover shipping costs for customers, if a customer refuses to provide the requested information (thus preventing delivery), they become responsible for those shipping fees.</p>

      <h2>Failed Deliveries</h2>
      <p>Please verify the shipping address you enter at checkout. The following conditions apply to failed deliveries:</p>
      <ul>
        <li><strong>Incorrect address:</strong> if the address is incorrect and a replacement must be sent, a replacement fee will be charged.</li>
        <li><strong>Delivered but not collected:</strong> if your package was delivered but not picked up, a replacement fee will be charged.</li>
        <li><strong>Lost in transit:</strong> the situation will be escalated to the shipping company and we will await their response. They are the party able to provide a replacement or refund — the outcome depends on their policy, not ours, as we do not carry out the deliveries.</li>
        <li><strong>Failed delivery coordination:</strong> if the shipping company attempts delivery and you do not coordinate with us or them successfully to receive the package, we will not be held responsible for issuing a refund. The customer is fully liable in this situation.</li>
        <li><strong>Changed address:</strong> if you change your address after placing the order, we cannot guarantee this will process through in time. Delivery will be attempted to whichever address is on the system under your name, and if you are not present we cannot be held liable or required to issue a refund.</li>
      </ul>
      <p>It is your responsibility to ensure that your address can receive the package and that you provide the correct details. We do not recommend using temporary addresses, as we cannot be held liable if you move from that address before the package arrives. We are not responsible for any delays in shipments, as we do not ship the packages ourselves.</p>

      <h2>Questions?</h2>
      <p>If you have any questions about returns, refunds, or cancellations, please contact us at <a href="mailto:contact@dainelylab.com">contact@dainelylab.com</a>. We usually respond within 1 business day.</p>

    </div>
  </div>
</section>
@endsection
