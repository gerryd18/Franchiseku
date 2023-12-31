<section class="registerFranchisor d-flex align-items-center" id="registerFranchisor">
    <div class="container bg-light bg-opacity-80 rounded mt-4 p-4" data-aos="fade-up" data-aos-duration="800">
        <div class="row d-flex align-items-center h-100">
            <div class="col-md-6  p-4" data-aos="fade-right" data-aos-duration="800">
                <h1 class="fs-1 text-primary mb-5 fw-bold ">Become One of Our Franchisor</h1>
                <p class="fw-light fs-5">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Cumque consectetur
                    qui
                    amet deleniti corrupti? Laborum obcaecati harum omnis perferendis numquam, quos at sit similique
                    error molestiae officiis inventore ex nisi quasi mollitia modi odit iusto? Exercitationem reiciendis
                    expedita mollitia, iure velit suscipit, cum tempore qui assumenda vel aliquid omnis obcaecati.</p>
            </div>
            <div class="col-md-6  p-4">
                <form action="{{ route('store.franchisor') }}" method="POST" data-aos="fade-left"
                    data-aos-duration="800">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="username">Franchisor Name</label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="{{ old('username') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="{{ old('email') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber"
                            value="{{ old('phoneNumber') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" id="address" name="address"
                            value="{{ old('address') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <button type="submit" class="btn btn-lg p-4 pt-1 pb-1 btn-primary rounded mt-4"
                        data-aos="fade-up-left" data-aos-duration="800">Apply</button>
                </form>

            </div>
        </div>
    </div>
</section>
