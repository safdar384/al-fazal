@extends('layout.app')

@section('title', 'Edit Labour')

@section('content')
<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Edit Labour
                    </h3>
                    <p class="text-muted small mb-0">Update labour profile</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('Labour.index') }}">Labour</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-2">Please fix the following errors:</h6>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <form action="{{ route('Labour.update', $labour->id) }}" method="POST" id="editLabourForm">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-4">
                                        <h6 class="text-warning mb-3 pb-2 border-bottom">
                                            <i class="bi bi-person-lines-fill me-2"></i>
                                            Basic Information
                                        </h6>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Labour Code <span class="text-danger">*</span></label>
                                                <input type="text" name="labourCode" class="form-control @error('labourCode') is-invalid @enderror" value="{{ old('labourCode', $labour->labourCode) }}" required>
                                                @error('labourCode')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Labour Name <span class="text-danger">*</span></label>
                                                <input type="text" name="labourName" class="form-control @error('labourName') is-invalid @enderror" value="{{ old('labourName', $labour->labourName) }}" required>
                                                @error('labourName')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Labour Address <span class="text-danger">*</span></label>
                                                <textarea name="labourAddress" rows="2" class="form-control @error('labourAddress') is-invalid @enderror" required>{{ old('labourAddress', $labour->labourAddress) }}</textarea>
                                                @error('labourAddress')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <input type="hidden" name="labour_type" value="salary_labour">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Labour Catagory <span class="text-danger">*</span></label>
                                                <select name="labourCatagory" id="labourCatagory" class="form-select @error('labourCatagory') is-invalid @enderror" required>
                                                    <option value="">-- Select Catagory --</option>
                                                    <option value="ajent" {{ old('labourCatagory', $labour->labourCatagory) === 'ajent' ? 'selected' : '' }}>Ajent</option>
                                                    <option value="worker" {{ old('labourCatagory', $labour->labourCatagory) === 'worker' ? 'selected' : '' }}>Worker</option>
                                                    <option value="fitter" {{ old('labourCatagory', $labour->labourCatagory) === 'fitter' ? 'selected' : '' }}>Fitter</option>
                                                </select>
                                                @error('labourCatagory')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6" id="commissionWrap" style="display: none;">
                                                <label class="form-label fw-semibold">Commission %</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0" max="100" name="commissionPercentage" id="commissionInput" class="form-control @error('commissionPercentage') is-invalid @enderror" value="{{ old('commissionPercentage', $labour->commissionPercentage) }}">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                                @error('commissionPercentage')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-4">
                                        <h6 class="text-success mb-3 pb-2 border-bottom">
                                            <i class="bi bi-cash-stack me-2"></i>
                                            Financial Information
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Sallery <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rs.</span>
                                                    <input type="number" step="0.01" min="0" name="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary', $labour->salary) }}" required>
                                                </div>
                                                @error('salary')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Addvance <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rs.</span>
                                                    <input type="number" step="0.01" min="0" name="advance" class="form-control @error('advance') is-invalid @enderror" value="{{ old('advance', $labour->advance) }}" required>
                                                </div>
                                                @error('advance')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Balance <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rs.</span>
                                                    <input type="number" step="0.01" name="balance" class="form-control @error('balance') is-invalid @enderror" value="{{ old('balance', $labour->balance) }}" required>
                                                </div>
                                                @error('balance')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <div class="alert alert-info mb-0">
                                                    <i class="bi bi-lightbulb me-2"></i>
                                                    Commission is applicable only when category is <strong>Ajent</strong>.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-footer bg-light py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bi bi-clock-history me-1"></i>
                                            Update and save labour details carefully
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                        <a href="{{ route('Labour.index') }}" class="btn btn-light me-2">
                                            <i class="bi bi-arrow-left me-1"></i> Back to List
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-warning text-white px-4">
                                            <i class="bi bi-save me-1"></i> Update Labour
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    (function() {
        const category = document.getElementById('labourCatagory');
        const commissionWrap = document.getElementById('commissionWrap');
        const commissionInput = document.getElementById('commissionInput');

        function toggleCommission() {
            const show = category.value === 'ajent';
            commissionWrap.style.display = show ? 'block' : 'none';
            commissionInput.required = show;
            if (!show) commissionInput.value = '';
        }

        category.addEventListener('change', toggleCommission);
        toggleCommission();
    })();
</script>
@endsection

