@extends('layouts.shop')

@section('title', 'Payment')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Payment'])

    <section class="flat-spacing-11">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="tf-page-cart-item">
                        <h5 class="fw-5 mb_20">Payment Details</h5>
                        <p><strong>Total Amount: &#8377; {{ number_format($total, 2) }}</strong></p>

                        @include('shop.partials.flash')

                        <p class="text_black-2 mb_20">
                            This is a demonstration checkout. No card is charged and no card details are stored.
                        </p>

                        <form method="POST" action="{{ route('checkout.pay') }}"
                            class="form-checkout tf-page-cart-checkout" id="payment-form">
                            @csrf
                            <fieldset class="fieldset">
                                <label for="card_number">Card Number</label>
                                <input type="text" name="card_number" id="card_number"
                                    placeholder="1234 5678 9012 3456" maxlength="19" inputmode="numeric" required>
                            </fieldset>
                            <div class="box grid-2 mt-2 mb-0">
                                <fieldset class="fieldset">
                                    <label for="expiry_date">Expiry Date (MM/YY)</label>
                                    <input type="text" name="expiry_date" id="expiry_date" placeholder="MM/YY"
                                        maxlength="5" inputmode="numeric" required>
                                </fieldset>
                                <fieldset class="fieldset">
                                    <label for="cvv">CVV</label>
                                    <input type="password" name="cvv" placeholder="123" maxlength="3"
                                        inputmode="numeric" required>
                                </fieldset>
                            </div>

                            <button type="submit"
                                class="tf-btn radius-3 btn-fill btn-icon animate-hover-btn justify-content-center mt-3">
                                Pay Now
                            </button>

                            @include('partials.storefront.payment-marks')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.getElementById('card_number').addEventListener('input', function(e) {
            var digits = e.target.value.replace(/\D/g, '').slice(0, 16);
            e.target.value = digits.match(/.{1,4}/g)?.join(' ') || '';
        });

        document.getElementById('expiry_date').addEventListener('input', function(e) {
            var value = e.target.value.replace(/[^\d\/]/g, '');

            if (value.length === 2 && !value.includes('/')) {
                value = value + '/';
            }

            e.target.value = value.slice(0, 5);
        });

        document.getElementById('expiry_date').addEventListener('blur', function(e) {
            var value = e.target.value;
            if (!value) return;

            var parts = value.split('/').map(function(n) { return parseInt(n, 10); });
            var month = parts[0], year = parts[1];
            var now = new Date();
            var thisMonth = now.getMonth() + 1;
            var thisYear = parseInt(now.getFullYear().toString().slice(-2), 10);

            if (!month || !year || month < 1 || month > 12) {
                alert('Please enter a valid month (01-12)');
                e.target.value = '';
                e.target.focus();
                return;
            }

            if (year < thisYear || (year === thisYear && month < thisMonth)) {
                alert('Expiry date must be equal to or later than the current month and year.');
                e.target.value = '';
                e.target.focus();
            }
        });
    </script>
@endpush
