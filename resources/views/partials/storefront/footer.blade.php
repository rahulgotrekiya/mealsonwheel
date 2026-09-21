<footer id="footer" class="footer background-black">
    <div class="footer-wrap wow fadeIn" data-wow-delay="0s">
        <div class="footer-body">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="footer-infor">
                            <div class="footer-logo">
                                <a href="{{ route('home') }}">
                                    <img src="{{ asset('assets/images/logo/logo-light.svg') }}" alt="Meals on Wheels">
                                </a>
                            </div>
                            <ul>
                                <li>
                                    <p>Address: B P College Of Computer Studies, Gandhinagar</p>
                                </li>
                                <li>
                                    <p>Email: <a
                                            href="mailto:support@mealsonwheels.com">support@mealsonwheels.com</a></p>
                                </li>
                                <li>
                                    <p>Phone: <a href="tel:+919123654780">9123654780</a></p>
                                </li>
                            </ul>
                            <ul class="tf-social-icon d-flex gap-10 style-white">
                                <li><a href="#" class="box-icon w_34 round social-facebook social-line"><i
                                            class="icon fs-14 icon-fb"></i></a></li>
                                <li><a href="#" class="box-icon w_34 round social-twiter social-line"><i
                                            class="icon fs-12 icon-Icon-x"></i></a></li>
                                <li><a href="#" class="box-icon w_34 round social-instagram social-line"><i
                                            class="icon fs-14 icon-instagram"></i></a></li>
                                <li><a href="#" class="box-icon w_34 round social-pinterest social-line"><i
                                            class="icon fs-14 icon-pinterest-1"></i></a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 col-12 footer-col-block">
                        <div class="footer-heading footer-heading-desktop">
                            <h6>Quick Links</h6>
                        </div>
                        <div class="footer-heading footer-heading-moblie">
                            <h6>Quick Links</h6>
                        </div>
                        <ul class="footer-menu-list tf-collapse-content">
                            <li><a href="{{ route('home') }}" class="footer-menu_item">Home</a></li>
                            <li><a href="{{ route('shop') }}" class="footer-menu_item">Shop</a></li>
                            <li><a href="{{ route('about') }}" class="footer-menu_item">About Us</a></li>
                            <li><a href="{{ route('contact') }}" class="footer-menu_item">Contact Us</a></li>
                        </ul>
                    </div>

                    <div class="col-xl-3 col-md-6 col-12 footer-col-block">
                        <div class="footer-heading footer-heading-desktop">
                            <h6>Info Links</h6>
                        </div>
                        <div class="footer-heading footer-heading-moblie">
                            <h6>Info Links</h6>
                        </div>
                        <ul class="footer-menu-list tf-collapse-content">
                            <li><a href="{{ route('privacy') }}" class="footer-menu_item">Privacy Policy</a></li>
                            <li><a href="{{ route('terms') }}" class="footer-menu_item">Terms &amp; Conditions</a></li>
                            <li><a href="{{ route('merchant.register') }}" class="footer-menu_item">Become a Supplier</a></li>
                        </ul>
                    </div>

                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="footer-newsletter footer-col-block">
                            <div class="footer-heading footer-heading-desktop">
                                <h6>Sign Up for Email</h6>
                            </div>
                            <div class="footer-heading footer-heading-moblie">
                                <h6>Sign Up for Email</h6>
                            </div>
                            <div class="tf-collapse-content">
                                <div class="footer-menu_item">Sign up to get first dibs on new arrivals, sales,
                                    exclusive content, events and more!</div>
                                <form class="form-newsletter" id="subscribe-form" method="POST"
                                    action="{{ route('newsletter.store') }}" accept-charset="utf-8">
                                    @csrf
                                    <div id="subscribe-content">
                                        <fieldset class="email">
                                            <input type="email" name="email" id="subscribe-email"
                                                placeholder="Enter your email...." tabindex="0" aria-required="true"
                                                required>
                                        </fieldset>
                                        <div class="button-submit">
                                            <button id="subscribe-button"
                                                class="tf-btn btn-sm radius-3 btn-fill btn-icon animate-hover-btn"
                                                type="submit">Subscribe<i class="icon icon-arrow1-top-left"></i></button>
                                        </div>
                                    </div>
                                    <div id="subscribe-msg">
                                        @if (session('newsletter'))
                                            <span class="text-success">{{ session('newsletter') }}</span>
                                        @endif
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div
                            class="footer-bottom-wrap d-flex gap-20 flex-wrap justify-content-between align-items-center">
                            <div class="footer-menu_item">&copy; {{ date('Y') }} Meals On Wheels. All Rights Reserved
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
