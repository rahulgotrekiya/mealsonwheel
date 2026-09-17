@extends('layouts.shop')

@section('title', 'About Us')

@section('content')
    @include('partials.storefront.page-title', ['title' => 'About Us'])

<section class="flat-spacing-9">
    <div class="container">
        <div class="flat-title my-0">
            <span class="title">Welcome to Meals On Wheels !</span>
            <p class="sub-title text_black-2">
                Welcome to Meals On Wheels, your one-stop solution for premium pet food delivered straight to your
                doorstep
                <br class="d-xl-block d-none">
                As part of our college project, we've created this platform with a simple mission: <br
                    class="d-xl-block d-none">
                provide pet parents with a convenient and reliable way to ensure their
                furry friends get the best nutrition. <br class="d-xl-block d-none">
            </p>
            <p class="sub-title text_black-2">At Meals On Wheels, we believe that happy pets make happy homes. <br
                    class="d-xl-block d-none">That's why we offer a carefully curated selection of high-quality pet
                food, <br class="d-xl-block d-none">tailored to meet the dietary needs of cats, dogs, and other beloved
                animals.
                <br class="d-xl-block d-none">From nutritious dry kibble to delicious wet meals and specialized treats,
                we've got it all covered.
            </p>

            <p class="sub-title text_black-2">Our platform is designed to make your shopping experience quick, easy, and
                stress-free.
                <br class="d-xl-block d-none">With user-friendly navigation, secure checkout options, and timely
                delivery,
                <br class="d-xl-block d-none">we aim to bring convenience to your fingertips while ensuring the health
                and well-being of
                your pets.
                <br class="d-xl-block d-none">
            </p>

            <p class="sub-title text_black-2">Join us on this journey to make pet parenting a joyful and hassle-free
                experience. At Meals On Wheels, your pets are our priority!
            </p>
        </div>
    </div>
@endsection
