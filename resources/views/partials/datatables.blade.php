{{--
    DataTables for any <table class="js-datatable">. Include once per page; loads only where used.
    Per-table options use DataTables' own data attributes, e.g.
      data-paging="false" data-searching="false" data-info="false" data-page-length="25"
      data-order='[[2, "desc"]]'   (default: keep the order the server sent)
      data-empty="No orders yet."  (message when there are no rows)
    Per-column: <th data-orderable="false" data-searchable="false">.
    Per-cell sort/search values: <td data-order="1404000" data-search="...">.
--}}
@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/datatables.net@2.1.8/js/dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.1.8/js/dataTables.bootstrap5.min.js"></script>
        <script>
        document.querySelectorAll('table.js-datatable').forEach((table) => {
            const hasOrder = table.hasAttribute('data-order');
            new DataTable(table, {
                ...(hasOrder ? {} : { order: [] }),
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                autoWidth: false,
                language: {
                    search: '',
                    searchPlaceholder: 'Search…',
                    emptyTable: table.dataset.empty || 'No data available.',
                    zeroRecords: 'No matching records found.',
                },
            });
        });
        </script>
    @endpush
@endonce
