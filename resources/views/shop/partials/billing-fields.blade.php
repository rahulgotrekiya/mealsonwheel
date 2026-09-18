<div class="box grid-2 mb-4">
    <fieldset class="fieldset">
        <label for="firstname">First Name</label>
        <input type="text" id="firstname" name="firstname"
            value="{{ old('firstname', auth()->user()->firstname) }}" required>
    </fieldset>
    <fieldset class="fieldset">
        <label for="lastname">Last Name</label>
        <input type="text" id="lastname" name="lastname" value="{{ old('lastname', auth()->user()->lastname) }}"
            required>
    </fieldset>
</div>
<div class="box grid-2 mb-4">
    <fieldset class="fieldset">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required>
    </fieldset>
    <fieldset class="fieldset">
        <label for="email">Email</label>
        <input type="text" id="email" value="{{ auth()->user()->email }}" disabled>
    </fieldset>
</div>
<div class="box grid-2 mb-4">
    <fieldset class="fieldset">
        <label for="street">Address</label>
        <input type="text" id="street" name="street" value="{{ old('street', $address?->street) }}" required>
    </fieldset>
    <fieldset class="fieldset">
        <label for="city">City</label>
        <input type="text" id="city" name="city" value="{{ old('city', $address?->city) }}" required>
    </fieldset>
</div>
<div class="box grid-2 mb-4">
    <fieldset class="fieldset">
        <label for="state">State</label>
        <input type="text" id="state" name="state" value="{{ old('state', $address?->state) }}" required>
    </fieldset>
    <fieldset class="fieldset">
        <label for="zip_code">Zip Code</label>
        <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code', $address?->zip_code) }}" required>
    </fieldset>
</div>
