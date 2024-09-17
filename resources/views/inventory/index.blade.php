@extends('layouts.global')

@section('title')
    Inventori
@endsection

@section('style')
    <style>
         .font-weight-bold {
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Halaman Inventori</h1>

    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Tabel Inventori</h3>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <table id="inventories-table" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Bahan Baku</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Jumlah</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function loadTable(categoryId = '') {
                $('#inventories-table').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    paging: false,
                    info: false,
                    ordering: false,
                    ajax: {
                        url: "{{ route('detail_inventory.index', ['category_id' => '']) }}/" + categoryId,
                        data: function(d) {
                            d.category_id = categoryId;
                        }
                    },
                    columns: [
                        { data: 'no', name: 'no' },
                        { data: 'nama_bahan_baku', name: 'nama_bahan_baku' },
                        { data: 'tanggal_masuk', name: 'tanggal_masuk' },
                        { data: 'jumlah', name: 'jumlah' },
                        { data: 'total', name: 'total' }
                    ],
                    columnDefs: [
                        { targets: [0, 1, 4], orderable: false }
                    ],
                    createdRow: function (row, data, dataIndex) {
                        $('td:eq(3)', row).addClass('font-weight-bold'); // jumlah column
                        $('td:eq(4)', row).addClass('font-weight-bold'); // total column
                    }
                });
            }

            var initialCategoryId = "{{ $category_id ?? '' }}";
            loadTable(initialCategoryId);

            $('.category-link').on('click', function (e) {
                e.preventDefault();
                var categoryId = $(this).attr('href').split('/').pop();
                $('#inventories-table').DataTable().destroy();
                loadTable(categoryId);

                // Update the URL without reloading the page
                if (history.pushState) {
                    history.pushState(null, null, "{{ url('detail_inventory') }}/" + categoryId);
                } else {
                    location.hash = "#!" + categoryId;
                }

                // Remove the active class from all links and add it to the clicked one
                $('.category-link').closest('li').removeClass('active');
                $(this).closest('li').addClass('active');
            });
        });
    </script>
@endsection
