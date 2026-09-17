@extends('layouts.shop')

@section('title', 'Contact Us')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'Contact Us'])

    <section class="flat-spacing-21">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success mb_20"
                    style="padding: 15px; background-color: #dff0d8; color: #3c763d; border-radius: 4px;">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb_20"
                    style="padding: 15px; background-color: #f2dede; color: #a94442; border-radius: 4px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="tf-grid-layout gap30 lg-col-2">
                <div class="tf-content-left">
                    <div class="mb_20">
                        <p class="mb_15"><strong>Address</strong></p>
                        <p>B P College Of Computer Studies, Gandhinagar</p>
                    </div>
                    <div class="mb_20">
                        <p class="mb_15"><strong>Phone</strong></p>
                        <p>9123654780</p>
                    </div>
                    <div class="mb_20">
                        <p class="mb_15"><strong>Email</strong></p>
                        <p>support@mealsonwheels.com</p>
                    </div>
                    <div>
                        <ul class="tf-social-icon d-flex gap-20 style-default">
                            <li><a href="#" class="box-icon link round social-facebook border-line-black"><i
                                        class="icon fs-14 icon-fb"></i></a></li>
                            <li><a href="#" class="box-icon link round social-twiter border-line-black"><i
                                        class="icon fs-12 icon-Icon-x"></i></a></li>
                            <li><a href="#" class="box-icon link round social-instagram border-line-black"><i
                                        class="icon fs-14 icon-instagram"></i></a></li>
                            <li><a href="#" class="box-icon link round social-pinterest border-line-black"><i
                                        class="icon fs-14 icon-pinterest-1"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="tf-content-right">
                    <h5 class="mb_20">Get in Touch</h5>
                    <p class="mb_24">If you&rsquo;ve got great products your making or looking to work with us then drop
                        us a line.</p>
                    <div>
                        <form class="form-contact" id="contactform" action="{{ route('contact.store') }}" method="POST">
                            @csrf
                            <div class="d-flex gap-15 mb_15">
                                <fieldset class="w-100">
                                    <input type="text" name="name" id="name" required placeholder="Name *"
                                        value="{{ old('name', auth()->user()?->full_name) }}" />
                                </fieldset>
                                <fieldset class="w-100">
                                    <input type="email" name="email" id="email" required placeholder="Email *"
                                        value="{{ old('email', auth()->user()?->email) }}" />
                                </fieldset>
                            </div>
                            <div class="mb_15">
                                <textarea placeholder="Message" name="message" id="message" required cols="30" rows="10">{{ old('message') }}</textarea>
                            </div>
                            <div class="send-wrap">
                                <button type="submit"
                                    class="tf-btn w-100 radius-3 btn-fill animate-hover-btn justify-content-center">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="w-100">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3666.076247589087!2d72.6551633!3d23.2403123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395c2b9bc02e8803%3A0x48d245a8f0271504!2sBholabhai%20Patel%20College%20of%20Computer%20Studies!5e0!3m2!1sen!2sin!4v1738079932128!5m2!1sen!2sin"
            width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
@endsection
