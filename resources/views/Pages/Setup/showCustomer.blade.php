@extends('layout.app')

@section('title', 'View Customer')

@section('content')
@php
    $fmtMoney = fn ($n) => 'Rs. '.number_format((float) ($n ?? 0), 0);
    $val = fn ($v) => filled($v) ? $v : '—';
@endphp

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        View Customer
                    </h3>
                    <p class="text-muted small mb-0">Customer profile (read-only)</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('Customer.index') }}">Customers</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3" style="background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%); color:#fff;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3">
                                    <i class="bi bi-shop-window fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">{{ $val($customer->bussnessName) }}</h5>
                                    <small class="opacity-75">
                                        <i class="bi bi-upc-scan me-1"></i>{{ $val($customer->custCode) }}
                                        |
                                        <i class="bi bi-geo-alt me-1"></i>{{ $val($customer->custArea) }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="d-flex justify-content-md-end gap-3">
                                <div class="text-center">
                                    <small class="d-block opacity-75">Balance</small>
                                    <h5 class="mb-0 fw-bold">{{ $fmtMoney($customer->custBalance) }}</h5>
                                </div>
                                <div class="text-center">
                                    <small class="d-block opacity-75">Credit</small>
                                    <h5 class="mb-0 fw-bold">{{ $fmtMoney($customer->custCreditLimit) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h6 class="text-primary mb-3 pb-2 border-bottom">
                                <i class="bi bi-info-circle me-2"></i>Basic Information
                            </h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted">Area</dt>
                                <dd class="col-sm-8 fw-semibold">{{ $val($customer->custArea) }}</dd>

                                <dt class="col-sm-4 text-muted">Code</dt>
                                <dd class="col-sm-8 fw-semibold font-monospace">{{ $val($customer->custCode) }}</dd>

                                <dt class="col-sm-4 text-muted">Business Name</dt>
                                <dd class="col-sm-8 fw-semibold">{{ $val($customer->bussnessName) }}</dd>

                                <dt class="col-sm-4 text-muted">Customer Name</dt>
                                <dd class="col-sm-8 fw-semibold">{{ $val($customer->custName) }}</dd>

                                <dt class="col-sm-4 text-muted">Address</dt>
                                <dd class="col-sm-8">{{ $val($customer->custAddress) }}</dd>

                                <dt class="col-sm-4 text-muted">City</dt>
                                <dd class="col-sm-8">{{ $val($customer->custCity) }}</dd>

                                <dt class="col-sm-4 text-muted">Contact</dt>
                                <dd class="col-sm-8">{{ $val($customer->custContect) }}</dd>

                                <dt class="col-sm-4 text-muted">Page</dt>
                                <dd class="col-sm-8">{{ $val($customer->custPage) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h6 class="text-success mb-3 pb-2 border-bottom">
                                <i class="bi bi-cash-stack me-2"></i>Financial Information
                            </h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-5 text-muted">Category</dt>
                                <dd class="col-sm-7 fw-semibold">{{ $val($customer->custCatagory) }}</dd>

                                <dt class="col-sm-5 text-muted">Discount (%)</dt>
                                <dd class="col-sm-7">{{ number_format((float) ($customer->custdiscount ?? 0), 2) }}%</dd>

                                <dt class="col-sm-5 text-muted">Payment Term</dt>
                                <dd class="col-sm-7">{{ $val($customer->custPayTermDay) }} days</dd>

                                <dt class="col-sm-5 text-muted">Credit Limit</dt>
                                <dd class="col-sm-7">{{ $fmtMoney($customer->custCreditLimit) }}</dd>

                                <dt class="col-sm-5 text-muted">Opening Balance</dt>
                                <dd class="col-sm-7">{{ $fmtMoney($customer->custBalance) }}</dd>

                                <dt class="col-sm-5 text-muted">Opening Date</dt>
                                <dd class="col-sm-7">
                                    {{ filled($customer->custOppDate) ? \Carbon\Carbon::parse($customer->custOppDate)->format('d-M-Y') : '—' }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="{{ route('Customer.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to List
                </a>
                <a href="{{ route('Customer.edit', $customer->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i>Edit Customer
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
