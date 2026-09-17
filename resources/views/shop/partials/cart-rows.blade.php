@foreach ($lines as $line)
    @php($product = $line['product'])
    <tr class="tf-cart-item file-delete">
        <td class="tf-cart-item_product">
            <a href="{{ route('product', $product) }}" class="img-box">
                <img src="{{ asset($product->primary_image) }}" alt="{{ $product->name }}">
            </a>
            <div class="cart-info">
                <p>{{ $product->name }}</p>
                <div class="cart-meta-variant">{{ $product->category->name }}</div>
                <span class="remove-cart link remove">
                    <button type="button" data-id="{{ $product->id }}"
                        class="btn p-0 remove-cart link text-danger cart_delete">Remove</button>
                </span>
            </div>
        </td>
        <td class="tf-cart-item_price" cart-data-title="Price">
            <div class="cart-price">&#8377;{{ number_format($product->price, 2) }}</div>
        </td>
        <td class="tf-cart-item_quantity">
            <div class="cart-quantity">
                <div class="wg-quantity">
                    <span class="input-group-btn">
                        <button type="button" class="btn-quantity minus-btn minus"
                            data-id="{{ $product->id }}">-</button>
                    </span>
                    <input type="text" class="form-control" value="{{ $line['quantity'] }}"
                        id="qty_{{ $product->id }}" readonly>
                    <span class="input-group-btn">
                        <button type="button" class="btn-quantity plus-btn add"
                            data-id="{{ $product->id }}">+</button>
                    </span>
                </div>
            </div>
        </td>
        <td class="tf-cart-item_total">
            <div class="cart-total">&#8377; {{ number_format($line['subtotal'], 2) }}</div>
        </td>
    </tr>
@endforeach
<tr>
    <td colspan="3" align="right"><b>Total</b></td>
    <td><b>&#8377; {{ number_format($total, 2) }}</b></td>
</tr>
