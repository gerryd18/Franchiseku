@extends('layouts.app')

@section('title')
    Add Franchise | FranchiseKu
@endsection


@section('main')
    <section class="registerFranchise d-flex align-items-center pb-5" id="registerFranchise">
        <div class="container bg-light bg-opacity-80 rounded mt-4 p-4" data-aos="fade-down" data-aos-duration="800">
            <div class="row d-flex align-items-center h-100">
                <div class="col-md-6  p-4">
                    <h1 class="fs-1 text-primary mb-5 fw-bold ">Register Franchise</h1>
                    <p class="fw-light fs-5">Used and supported in over 178 countries around the globe. We'll work with you
                        to open more doors and help you look for your suitable customers</p>
                </div>
                <div class="col-md-6  p-4 ">
                    <form action="{{ route('store.franchise') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="franchiseName">Franchise Name</label>
                            <input type="text" class="form-control @error('franchiseName') is-invalid @enderror"
                                id="franchiseName" name="franchiseName" value="{{ old('franchiseName') }}">
                            @error('franchiseName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="franchiseLocation">Franchise Location</label>
                            <input type="text" class="form-control @error('franchiseLocation') is-invalid @enderror"
                                id="franchiseLocation" name="franchiseLocation" value="{{ old('franchiseLocation') }}">
                            @error('franchiseLocation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="franchiseCategory">Franchise Category</label>
                            <select class="form-control @error('franchiseCategory') is-invalid @enderror"
                                id="franchiseCategory" name="franchiseCategory">
                                <option>
                                    Choose..</option>
                                @foreach ($allFranchiseCategory as $item)
                                    <option
                                        value="{{ $item->id }}"{{ old('franchiseCategory') == $item->franchiseCategory ? 'selected' : '' }}>
                                        {{ $item->franchiseCategory }}</option>
                                @endforeach
                            </select>
                            @error('franchiseCategory')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="franchisePrice">Price</label>
                            <input type="number" class="form-control @error('franchisePrice') is-invalid @enderror"
                                placeholder="Rp" id="franchisePrice" name="franchisePrice"
                                value="{{ old('franchisePrice') }}">
                            @error('franchisePrice')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="franchiseReport">Report</label>
                            <input id="franchiseReport" accept=".pdf, .doc, .docx, .xls, .xlsx, .zip" name="franchiseReport"
                                type="file" class="form-control @error('franchiseReport') is-invalid @enderror">
                            @error('franchiseReport')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Franchise Image  --}}
                        <div class="form-group mb-3">
                            <label for="example-text-input" class="col-sm-10 col-form-label">Franchise Logo</label>
                            <div class="col-sm-10">
                                <input class="form-control" name="franchiseLogo" type="file" placeholder="Franchise Logo"
                                    accept=".png, .jpg, .jpeg" id="image">
                                @error('franchiseLogo')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Image Display  --}}
                        <div class="form-group mb-3">
                            <label for="example-text-input" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-10">
                                <img id="showImage" style="width: 128px" class="rounded avatar-lg"
                                    src="{{ url('upload/no_image.jpg') }}" alt="Card image cap">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="franchiseDesc" class="col-sm-4 col-form-label">Franchise Description</label>
                            <textarea class="form-control @error('franchiseDesc') is-invalid @enderror" name="franchiseDesc" id="franchiseDesc"
                                cols="30" rows="10"></textarea>
                            @error('franchiseDesc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-lg p-4 pt-1 pb-1 btn-primary rounded mt-4">Register
                            Franchise</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
