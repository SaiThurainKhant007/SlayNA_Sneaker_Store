@extends('layouts.admin')

@section('title', 'Customer Feedback')

@section('content')
<h1 class="h4 mb-3">Customer Feedback</h1>

<div class="card dashboard-card p-3 mb-3">
    <div class="table-responsive">
        <table class="table table-sm align-middle js-datatable" data-empty="No feedback yet." data-order='[[0, "desc"]]'>
            <thead><tr><th>Date</th><th>Customer</th><th>Rating</th><th>Feedback</th><th data-orderable="false" data-searchable="false"><span class="visually-hidden">Delete</span></th></tr></thead>
            <tbody>
                @foreach ($feedback as $entry)
                    <tr>
                        <td class="text-nowrap" data-order="{{ $entry->created_at->format('Y-m-d H:i:s') }}">{{ $entry->created_at->format('d M Y') }}</td>
                        <td>{{ $entry->customer->name }}<br><a href="mailto:{{ $entry->customer->email }}" class="small">{{ $entry->customer->email }}</a></td>
                        <td class="text-nowrap" data-order="{{ $entry->rating }}">
                            <span class="rating-stars" aria-hidden="true">{{ str_repeat('★', (int) $entry->rating) . str_repeat('☆', 5 - (int) $entry->rating) }}</span>
                            <span class="visually-hidden">{{ $entry->rating }} out of 5</span>
                        </td>
                        <td>{!! nl2br(e($entry->message)) !!}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.feedback.destroy', $entry) }}" onsubmit="return confirm('Delete this feedback? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
