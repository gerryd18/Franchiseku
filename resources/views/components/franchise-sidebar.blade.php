@vite('resources/css/sidebar.css')
@vite('resources/js/sidebar.js')
<div class="col-lg-3 col-md-3 col-sm-3 mb-3">
    <div class="accordion" id="accordionPanelsStayOpen" data-aos="fade" data-aos-duration="800">
        <a href="{{ route('franchise') }}" id="resetFilterButton"
            class="btn btn-danger w-100 border border-2 rounded rounded-2 mb-3 d-flex justify-content-center align-items-center fs-5 fw-light text-center">
            <span class="material-symbols-rounded">
                filter_alt_off
            </span>
            Reset Filter
        </a>
        <div class="accordion-item mb-3 border border-2 rounded">
            <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="false"
                    aria-controls="panelsStayOpen-collapseOne">
                    <h5>Category</h5>
                </button>
            </h2>
            <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse"
                aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                    @if ($franchiseCategories->count() == 0)
                        <div class="col-lg-12 pb-3" data-aos="fade-down-right" data-aos-duration="800">
                            <div class="alert alert-warning w-100">No categories to be found!</div>
                        </div>
                    @else
                    @endif
                    @foreach ($franchiseCategories as $item)
                        <div id="categoryList" class="row d-flex align-items-center">
                            <a href="{{ route('browse.all.franchise', ['category' => $item->id] + request()->except('category')) }}"
                                class="w-100 bg-transparent border-0 text-start fs-6 p-3">{{ $item->franchiseCategory }}</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="accordion-item border-top mb-3 border border-2 rounded">
            <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false"
                    aria-controls="panelsStayOpen-collapseTwo">
                    <h5>Price Range</h5>
                </button>
            </h2>
            <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse"
                aria-labelledby="panelsStayOpen-headingTwo">
                <div class="accordion-body">
                    <div id="priceList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['minPrice' => 0, 'maxPrice' => 50000000] + request()->except(['minPrice', 'maxPrice'])) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">IDR
                            0.00 - IDR 50,000,000.00</a>
                    </div>
                    <div id="priceList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['minPrice' => 50000000, 'maxPrice' => 150000000] + request()->except(['minPrice', 'maxPrice'])) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">IDR
                            50,000,000.00 - IDR 150,000,000.00</a>
                    </div>
                    <div id="priceList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['minPrice' => 150000000, 'maxPrice' => 250000000] + request()->except(['minPrice', 'maxPrice'])) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">IDR
                            150,000,000.00 - IDR 250,000,000.00</a>
                    </div>
                    <div id="priceList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['minPrice' => 250000000] + request()->except(['minPrice'])) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">Exceeds IDR 250,000,000.00</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="accordion-item border-top mb-3 border border-2 rounded">
            <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false"
                    aria-controls="panelsStayOpen-collapseThree">
                    <h5>Rating Range</h5>
                </button>
            </h2>
            <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse"
                aria-labelledby="panelsStayOpen-headingThree">
                <div class="accordion-body">
                    <div id="ratingList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['rating' => 1]) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">1 Star</a>
                    </div>
                    <div id="ratingList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['rating' => 2]) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">2 Star</a>
                    </div>
                    <div id="ratingList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['rating' => 3]) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">3 Star</a>
                    </div>
                    <div id="ratingList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['rating' => 4]) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">4 Star</a>
                    </div>
                    <div id="ratingList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['rating' => 5]) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">5 Star</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="accordion-item mb-3 border border-2 rounded">
            <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#panelsStayOpen-collapseFour" aria-expanded="false"
                    aria-controls="panelsStayOpen-collapseFour">
                    <h5>Procurement Status</h5>
                </button>
            </h2>
            <div id="panelsStayOpen-collapseFour" class="accordion-collapse collapse"
                aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                    <div id="procurementList" class="row d-flex align-items-center">
                        <a href="{{ route('browse.all.franchise', ['isPurchased' => 1] + request()->except('isPurchased')) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">Purchased</a>
                        <a href="{{ route('browse.all.franchise', ['isPurchased' => 0] + request()->except('isPurchased')) }}"
                            class="w-100 bg-transparent border-0 text-start fs-6 p-3">Available</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
