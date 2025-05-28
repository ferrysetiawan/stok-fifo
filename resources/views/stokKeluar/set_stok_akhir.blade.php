@extends('layouts.global')

@section('title')
Generate Stok
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2-bootstrap4.css') }}">
@endsection

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between">
        <h1>Halaman Generate Stok keluar</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">form generate</h3>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <form method="POST" action="{{ route('inventory.storeStokAkhir') }}" class="mb-4">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>Pilih Bahan Baku</label>
                                        <select class="form-control select2" id="bahanBaku" name="bahan_baku_id"></select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Target Stok Akhir Bulan</label>
                                        <input type="text" name="stok_akhir_bulan" class="form-control" min="0"
                                            required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Bulan</label>
                                        <input type="month" name="bulan" class="form-control" value="{{ date('Y-m') }}" required>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-success w-100">Tambah</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
<script src="{{ asset('assets/modules/select2/dist/js/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Function to handle AJAX errors
        function handleAjaxError(xhr) {
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors || xhr.responseJSON.error;

                // Jika error adalah string (contohnya: 'Stok tidak mencukupi.')
                if (typeof errors === 'string') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errors, // Menampilkan pesan error yang dikirim oleh server
                        showConfirmButton: true
                    });
                } else if (typeof errors === 'object') {
                    // Jika error adalah object (validasi form)
                    // Bersihkan pesan error lama
                    $('.invalid-feedback').remove();
                    $('.is-invalid').removeClass('is-invalid');

                    for (var key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            var errorMessage = errors[key][0];
                            var inputField = $('#' + key);

                            // Tandai input dengan is-invalid dan tambahkan pesan invalid-feedback
                            inputField.addClass('is-invalid');
                            if (inputField.next('.invalid-feedback').length === 0) {
                                inputField.after('<div class="invalid-feedback">' + errorMessage + '</div>');
                            }
                        }
                    }
                }
            } else {
                // Tangani error selain 422
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan!',
                    text: 'Terjadi kesalahan pada server. Silakan coba lagi.',
                    showConfirmButton: true
                });
                console.error(xhr.responseText); // Menangani error lain
            }
        }


        $("#bahanBaku").select2({
            ajax: {
                url: '/stok/bahanbaku',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term, // Parameter pencarian
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.id,
                                text: item.bahan_baku,
                                satuan: item.satuan
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1, // Jumlah karakter minimal sebelum pencarian dimulai
            placeholder: 'Pilih Bahan Baku',
            escapeMarkup: function (markup) {
                return markup;
            } // Mengizinkan markup HTML pada hasil pencarian
        });

        $("#bahanBaku").on('select2:select', function (e) {
            var data = e.params.data;
            var satuan = data.satuan; // Ambil satuan dari data yang dipilih

            // Tampilkan satuan di UI
            $("#satuanBahanBaku").text(satuan);
        });

        let isSubmitting = false;

        $("#pembelianForm").submit(function (e) {
            e.preventDefault();

            if (isSubmitting) return; // Prevent further submits
            isSubmitting = true; // Set flag to true

            // Disable the submit button to prevent multiple submits
            var $submitButton = $(this).find('button[type="submit"]');
            $submitButton.prop('disabled', true);

            // Serialize form data
            var formData = $(this).serialize();
            var bahanBakuId = $("#bahanBaku").val();
            formData += "&bahanBaku=" + bahanBakuId;

            // Clear previous styles and error messages
            $(".form-control").removeClass('is-invalid');
            $(".invalid-feedback").remove();

            // AJAX request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'BERHASIL!',
                        text: 'DATA BERHASIL DISIMPAN!',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(function () {
                        // $(".data-pembelian").load(location.href +
                        //     ' .data-pembelian')
                        var dataTable = $('#table-pembelian').DataTable();
                        dataTable.ajax.reload();
                        $('#jumlah').val('');
                        $('#bahanBaku').val('').trigger('change');
                    });
                },
                error: function (xhr, status, error) {
                    handleAjaxError(xhr);
                },
                complete: function () {
                    isSubmitting = false; // Allow submits again
                    $submitButton.prop('disabled', false);
                }
            });
        });
    });
</script>
@endsection
