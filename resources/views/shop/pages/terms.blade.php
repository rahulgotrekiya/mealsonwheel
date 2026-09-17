@extends('layouts.shop')

@section('title', 'Terms & Conditions')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Terms & Conditions'])

<section class="flat-spacing-25">
    <div class="container">
        <div class="tf-main-area-page tf-terms-conditions">
            <div class="box">
                <h4 class="mb-0">1. Introduction</h4>
                <p>Welcome to Meals On Wheels, your trusted platform for premium pet food and supplies. By accessing and
                    using our website, you agree to be bound by the following terms and conditions. Please read these
                    terms carefully before using our services.</p>

                <h4 class="mb-0">2. Acceptance of Terms</h4>
                <p>By visiting, browsing, or purchasing from our website, you agree to comply with and be bound by these
                    terms and conditions, along with our Privacy Policy. If you do not agree, please refrain from using
                    our website.</p>

                <h4 class="mb-0">3. Eligibility</h4>
                <p>
                    To make a purchase on our website, you must be at least 18 years old or have the consent of a parent
                    or guardian. By placing an order, you confirm that you meet this requirement.
                </p>
                <h4 class="mb-0">4. Products and Availability</h4>
                <p>We strive to ensure all product descriptions, images, and pricing are accurate. However, we do not
                    guarantee that product availability, descriptions, or prices will be error-free. We reserve the
                    right to correct errors, update information, or discontinue products at any time without notice.</p>

                <h4 class="mb-0">5. Orders and Payments</h4>
                <p>
                    - **Order Placement:** All orders are subject to acceptance and availability. You will receive a
                    confirmation email upon successful placement of your order.<br>
                    - **Payment Methods:** We accept all major credit/debit cards, online wallets, and other payment
                    gateways displayed at checkout. Payments must be completed before the shipment of products.<br>
                    - **Pricing:** Prices listed on the website include applicable taxes unless otherwise stated.
                </p>

                <h4 class="mb-0">6. Shipping and Delivery</h4>
                <p>
                    - **Shipping:** We ship to locations specified on our website. Delivery times are estimates and may
                    vary due to unforeseen circumstances.<br>
                    - **Delays:** Meals On Wheels is not responsible for delays caused by third-party carriers or
                    unforeseen events.<br>
                    - **Address Accuracy:** Customers are responsible for providing accurate delivery addresses. Meals
                    On Wheels will not be liable for failed deliveries due to incorrect addresses.
                </p>

                <h4 class="mb-0">7. Returns and Refunds</h4>
                <p>
                    - **Eligibility:** Products may be returned if they are damaged, defective, or incorrect, provided
                    they are reported within 7 days of delivery.<br>
                    - **Non-Returnable Items:** Perishable items such as pet food may not be eligible for returns unless
                    they arrive damaged or incorrect.<br>
                    - **Refund Process:** Refunds will be processed once the returned product is inspected and approved.
                    Refunds may take up to 7-10 business days.
                </p>

                <h4 class="mb-0">8. Cancellation Policy</h4>
                <p>
                    - **Order Cancellation:** Orders may be cancelled before they are shipped. Once an order has been shipped, it cannot be cancelled.<br>
                    - **Cancellation Process:** To cancel an order, please use the cancel order button in your account dashboard.<br>
                    - **Issues and Support:** If you experience any problems with cancellation or have other order-related concerns, please contact us through our <a href="{{ route('contact') }}">Contact Us</a> page.
                </p>

                <h4 class="mb-0">9. User Responsibilities</h4>
                <p>
                    - **Account Security:** Users are responsible for maintaining the confidentiality of their account
                    login details.<br>
                    - **Prohibited Actions:** Users agree not to misuse our website for fraudulent activities, spamming,
                    or introducing malicious software.
                </p>

                <h4 class="mb-0">10. Intellectual Property</h4>
                <p>All content, including images, logos, and text, on the Meals On Wheels website is the property of
                    Meals On Wheels or its licensors. Unauthorized use or reproduction of this content is prohibited.
                </p>

                <h4 class="mb-0">11. Limitation of Liability</h4>
                <p>Meals On Wheels is not liable for any indirect, incidental, or consequential damages arising from the
                    use or inability to use our website or products.</p>

                <h4 class="mb-0">12. Privacy Policy</h4>
                <p>Your privacy is important to us. Please refer to our <a href="{{ route('privacy') }}">Privacy Policy</a> to understand how
                    we collect, use, and protect your personal information.</p>

                <h4 class="mb-0">13. Modifications to Terms</h4>
                <p>We reserve the right to update these terms and conditions at any time. Users are encouraged to review
                    this page periodically for changes.</p>

                <h4 class="mb-0">14. Contact Us</h4>
                <p>If you have any questions or concerns regarding these Terms and Conditions, feel free to contact us:
                </p>
                <p>
                    <strong>Email:</strong> <a href="mailto:support@mealsonwheels.com">support@mealsonwheels.com</a><br>
                    <strong>Phone:</strong> 9123654780<br>
                    <strong>Address:</strong> B P College Of Computer Studies, Gandhinagar
                </p>


            </div>

        </div>
    </div>
    </div>
</section>
@endsection
