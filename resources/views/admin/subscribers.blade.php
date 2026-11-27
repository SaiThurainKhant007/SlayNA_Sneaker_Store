@extends('layouts.admin')

@section('title', 'Drop Alerts')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 mb-0">Drop Alerts</h1>
        <p class="text-muted small mb-0">Emails signed up in the website footer for new arrivals and restocks ({{ $subscribers->count() }} {{ Str::plural('subscriber', $subscribers->count()) }}).</p>
    </div>
    <a href="{{ route('admin.reports.export', 'subscribers') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Download CSV</a>
</div>

<div class="card dashboard-card p-3 mb-3">
    <div class="table-responsive">
        <table class="table table-sm align-middle js-datatable" data-empty="No one has signed up yet." data-order='[[0, "desc"]]'>
            <thead><tr><th>Signed Up</th><th>Email</th><th data-orderable="false" data-searchable="false"><span class="visually-hidden">Remove</span></th></tr></thead>
            <tbody>
                @foreach ($subscribers as $subscriber)
                    <tr>
                        <td class="text-nowrap" data-order="{{ $subscriber['signed_up'] }}">{{ \Illuminate\Support\Carbon::parse($subscriber['signed_up'])->format('d M Y') }}</td>
                        <td><a href="mailto:{{ $subscriber['email'] }}">{{ $subscriber['email'] }}</a></td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.subscribers.destroy') }}" onsubmit="return confirm('Remove this email from drop alerts?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="email" value="{{ $subscriber['email'] }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@include('partials.datatables')
@endsection
