@extends('layouts.app')

@section('title', 'Pending Approval')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body py-5">
                    @if($lawyer->status === 'pending')
                        <div class="mb-4">
                            <i class="bi bi-hourglass-split display-1 text-warning"></i>
                        </div>
                        <h3 class="mb-3">Account Pending Approval</h3>
                        <p class="text-muted mb-4">
                            Your lawyer account is currently under review. An administrator will review 
                            your application and approve your account shortly.
                        </p>
                        <div class="alert alert-info text-start">
                            <strong><i class="bi bi-info-circle me-2"></i>What happens next?</strong>
                            <ul class="mb-0 mt-2">
                                <li>An administrator will review your credentials</li>
                                <li>You'll receive notification once approved</li>
                                <li>You can then access your lawyer portal</li>
                            </ul>
                        </div>
                    @elseif($lawyer->status === 'rejected')
                        <div class="mb-4">
                            <i class="bi bi-x-circle display-1 text-danger"></i>
                        </div>
                        <h3 class="mb-3">Application Rejected</h3>
                        <p class="text-muted mb-4">
                            Unfortunately, your lawyer account application has been rejected.
                        </p>
                        @if($lawyer->rejection_reason)
                        <div class="alert alert-danger text-start">
                            <strong>Reason:</strong><br>
                            {{ $lawyer->rejection_reason }}
                        </div>
                        @endif
                        <p class="text-muted">
                            If you believe this is an error, please contact the Digos City Legal Office.
                        </p>
                    @elseif($lawyer->status === 'suspended')
                        <div class="mb-4">
                            <i class="bi bi-pause-circle display-1 text-secondary"></i>
                        </div>
                        <h3 class="mb-3">Account Suspended</h3>
                        <p class="text-muted mb-4">
                            Your lawyer account has been suspended. Please contact the administrator 
                            for more information.
                        </p>
                        @if($lawyer->suspension_reason)
                        <div class="alert alert-warning text-start">
                            <strong>Reason:</strong><br>
                            {{ $lawyer->suspension_reason }}
                        </div>
                        @endif
                    @endif

                    <hr class="my-4">

                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="bi bi-box-arrow-left me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
