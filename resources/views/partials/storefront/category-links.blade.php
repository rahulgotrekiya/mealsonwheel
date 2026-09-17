<ul class="nav-ul-mb" id="wrapper-menu-navigation">
    @foreach ($navCategories as $category)
        <li class="nav-mb-item">
            <a href="{{ url('/category/'.$category->slug) }}" class="tf-category-link mb-menu-link">
                <div class="image">
                    <img src="{{ asset($category->image) }}" alt="{{ $category->name }}">
                </div>
                <span class="link">{{ $category->name }}</span>
            </a>
        </li>
    @endforeach
</ul>
