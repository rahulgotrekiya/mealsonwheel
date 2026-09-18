@extends('layouts.shop')

@section('title', 'Cart')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Cart'])

    <section class="flat-spacing-11">
        <div class="container">
            <div class="tf-page-cart text-center mt_140 mb_200" style="display: none;">
                <h5 class="mb_24">Your cart is empty</h5>
                <p class="mb_24">You may check out all the available products and buy some in the shop</p>
                <a href="{{ route('shop') }}"
                    class="tf-btn btn-sm radius-3 btn-fill btn-icon animate-hover-btn">Return to shop<i
                        class="icon icon-arrow1-top-left"></i></a>
            </div>

            <div class="cart-wrap" style="display: none;">
                <div class="tf-page-cart-item">
                    <form>
                        <table class="tf-table-page-cart">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="tbody"></tbody>
                        </table>
                    </form>
                    <div class="cart-checkout-btn">
                        @auth
                            <a href="{{ route('checkout') }}"
                                class="tf-btn w-100 btn-fill animate-hover-btn radius-3 justify-content-center d-inline">
                                <span>Check out</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="tf-btn w-100 btn-fill animate-hover-btn radius-3 justify-content-center d-inline">
                                <span>Login To Checkout</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(function() {
            var max = {{ (int) config('marketplace.max_quantity_per_item') }};

            function refresh() {
                getDetails();
                getCart();
            }

            function setQuantity(id, qty) {
                $.post('{{ route('cart.update') }}', { id: id, qty: qty }, function(response) {
                    if (response.error) {
                        alert(response.message);
                    }
                    refresh();
                }, 'json');
            }

            $(document).on('click', '.cart_delete', function(e) {
                e.preventDefault();
                $.post('{{ route('cart.remove') }}', { id: $(this).data('id') }, refresh, 'json');
            });

            $(document).on('click', '.minus', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var qty = parseInt($('#qty_' + id).val(), 10);

                if (qty > 1) {
                    setQuantity(id, qty - 1);
                }
            });

            $(document).on('click', '.add', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var qty = parseInt($('#qty_' + id).val(), 10);

                if (qty < max) {
                    setQuantity(id, qty + 1);
                } else {
                    alert('You can only add a maximum of ' + max + ' units of this product.');
                }
            });

            getDetails();
        });

        function getDetails() {
            $.post('{{ route('cart.details') }}', function(response) {
                if (!response || response.trim() === '') {
                    $('.tf-page-cart').show();
                    $('.cart-wrap').hide();
                } else {
                    $('.tf-page-cart').hide();
                    $('.cart-wrap').show();
                    $('#tbody').html(response);
                }
                getCart();
            }, 'json');
        }
    </script>
@endpush
