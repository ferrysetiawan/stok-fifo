@extends('layouts.global')

@section('title')
    Bahan Baku
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Halaman Bahan Baku</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Tabel Bahan Baku</h3>
                        <span>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importModal">
                            Import Data
                        </button>
                        <button type="button" class="btn btn-light" data-toggle="modal" data-target="#addModal">
                            Tambah Data
                        </button>
                        </span>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <table class="table table-bordered table-md" id="table-menu">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Bahan Baku</th>
                                        <th>Satuan</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
{{-- import modal --}}
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Import Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form untuk menambahkan data -->
                <form autocomplete="off" id="importForm">
                    <div class="form-group">
                        <label for="field_name">File</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                        @error('file')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <!-- Tambahkan input lainnya jika diperlukan -->
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- end import modal --}}
{{-- create modal --}}
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Tambah Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form untuk menambahkan data -->
                <form autocomplete="off" id="addForm">
                    <div class="form-group">
                        <label for="field_name">Nama Bahan Baku</label>
                        <input type="text" class="form-control @error('bahan_baku') is-invalid @enderror" id="bahan_baku" name="bahan_baku">
                        @error('bahan_baku')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="kategori_id">Kategori</label>
                        <select name="kategori_id" class="form-control @error('nama_menu') is-invalid @enderror" id="kategori_id">
                            <option value="">-- pilih kategori --</option>
                            @foreach ($kategori as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="field_name">Satuan</label>
                        <input type="text" class="form-control @error('satuan') is-invalid @enderror" id="satuan" name="satuan">
                        @error('satuan')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="field_name">Harga</label>
                        <input type="number" class="form-control @error('harga') is-invalid @enderror" id="harga" name="harga">
                        @error('harga')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <!-- Tambahkan input lainnya jika diperlukan -->
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- end create modal --}}
{{-- edit modal --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" id="editForm" data-id="" action="#" method="POST">
                    <div class="form-group">
                        <label for="field_name">Bahan Baku</label>
                        <input type="text" class="form-control @error('bahan_baku') is-invalid @enderror" id="nama_menuan" name="bahan_baku">
                        @error('bahan_baku')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select name="kategori_id" class="form-control @error('nama_menu') is-invalid @enderror" id="category_idan">
                            <option value="">-- pilih kategori --</option>
                            @foreach ($kategori as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="field_name">Satuan</label>
                        <input type="text" class="form-control @error('satuan') is-invalid @enderror" id="satuanan" name="satuan">
                        @error('satuan')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="field_name">Harga</label>
                        <input type="number" class="form-control @error('harga') is-invalid @enderror" id="hargaan" name="harga">
                        @error('harga')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <!-- Tambahkan input lainnya jika diperlukan -->
                    <button type="button" class="btn btn-primary" onclick="updateData()">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- end edit modal --}}
@endsection

@section('js')

    <script>
        function openEditModal(id) {
            // console.log("ID yang diterima:", id);
            //Dapatkan data dari server berdasarkan ID
            $.ajax({
                url: '{{ url("bahan_baku") }}/' + id + '/edit',
                type: 'GET',
                success: function (response) {

                    console.log(response);

                    if (response) {
                        // Isi formulir modal dengan data yang diterima dari server
                        $('#editForm').attr('data-id', response.id); // Simpan ID pengeluaran
                        $('#nama_menuan').val(response.bahan_baku);
                        $('#hargaan').val(response.harga);
                        $('#satuanan').val(response.satuan);
                        // Set opsi kategori terpilih
                        $('#category_idan option[value="' + response.kategori_id + '"]').attr('selected', true);
                        $('#editModal').modal('show');
                    } else {
                        console.error('Data tidak ditemukan atau struktur respons tidak sesuai.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
        function updateData() {
            // Dapatkan ID pengeluaran dari atribut data-id
            var kategoriId = $('#editForm').data('id');

            // Dapatkan data formulir modal
            var formData = {
                bahan_baku: $('#nama_menuan').val(),
                harga: $('#hargaan').val(),
                satuan: $('#satuanan').val(),
                kategori_id: $('#category_idan').val(),
            };

            $(".form-control").removeClass('is-invalid');
            $(".invalid-feedback").remove();

            // Lakukan pembaruan melalui AJAX
            $.ajax({
                url: '/bahan_baku/' + kategoriId,
                type: 'PUT',
                data: formData,
                success: function (response) {
                    if (response.status == "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'BERHASIL!',
                            text: 'DATA BERHASIL DIUBAH!',
                            showConfirmButton: false,
                            timer: 3000
                        }).then(function () {
                            $('#editModal').modal('hide');
                            location.reload();
                            // var dataTable = $('#table-menu').DataTable();
                            // dataTable.ajax.reload();
                        });
                        // Tampilkan pesan sukses atau lakukan tindakan lainnya
                        console.log('Pembaruan berhasil!');
                    } else {
                        console.error('Pembaruan gagal: ' + (response.message || 'Terjadi kesalahan.'));
                    }
                },
                error: function (xhr, status, error) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var idMappings = {
                            'bahan_baku': 'nama_menuan',
                            'harga': 'hargaan',
                            'kategori_id': 'category_idan',
                            'satuan': 'satuanan',
                        };
                        // Display validation errors
                        if (typeof errors === 'object' && errors !== null) {
                            for (var key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    var errorMessage = errors[key][0];
                                    var fieldId = idMappings[key] || key;
                                    $('#' + fieldId).addClass('is-invalid');
                                    $('#' + fieldId).after('<div class="invalid-feedback">' + errorMessage + '</div>');
                                }
                            }
                        } else {
                            console.error('Validation error response format is unexpected.');
                        }
                    } else {
                        console.error(xhr.responseText);
                    }
                }
            });
        }


        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function handleAjaxError(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    // Display validation errors
                    for (var key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            var errorMessage = errors[key][0];
                            $('#' + key).addClass('is-invalid');
                            $('#' + key).after('<div class="invalid-feedback">' + errorMessage + '</div>');
                        }
                    }
                } else {
                    console.error(xhr.responseText);
                }
            }

            var table = $('#table-menu').DataTable({
                // processing: true,
                serverSide: true,
                responsive: true,
                searching: true,
                paging: true,
                info: false,
                ordering: false,
                ajax: {
                    url: "{{ route('bahan_baku.index') }}",
                },
                columns: [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1; // Menampilkan nomor urut
                        }
                    },
                    { data: 'bahan_baku', name: 'bahan_baku' },
                    { data: 'satuan', name:'satuan' },
                    { data: 'kategori.nama', name: 'kategori_id' },
                    {
                        data: 'harga',
                        name: 'harga',
                        render: function (data, type, row) {
                            return formatRupiah(data);
                        }
                    },
                    {
                        data: null,
                        render: function (data) {
                            return '<button class="btn btn-warning mr-1" data-toggle="tooltip" data-placement="bottom" title="ubah" onclick="openEditModal(' + data.id + ')" id="editBtn' + data.id + '"><i class="fas fa-pencil-alt"></i></button>' +
                                '<button class="btn btn-danger" data-toggle="tooltip" data-placement="bottom" title="hapus" onclick="destroy(' + data.id + ')" id="' + data.id + '"><i class="fas fa-trash"></i></button>';
                        }
                    }
                ]
            });


            $('#addForm').submit(function(e) {
                e.preventDefault();

                var formData = $(this).serialize();

                // Clear previous styles and error messages
                $(".form-control").removeClass('is-invalid');
                $(".invalid-feedback").remove();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('bahan_baku.store') }}',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'BERHASIL!',
                            text: 'DATA BERHASIL DISIMPAN!',
                            showConfirmButton: false,
                            timer: 3000
                        }).then(function() {
                            $('#addModal').modal('hide');
                            var dataTable = $('#table-menu').DataTable();
                            dataTable.ajax.reload();
                            $('#bahan_baku').val('');
                            $('#harga').val('');
                            $('#kategori_id').val('');
                            $('#satuan').val('');
                        });

                    },
                    error: function(xhr, status, error) {
                        handleAjaxError(xhr);
                    }
                });
            });

            $('#importForm').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                $.ajax({
                    url: "{{ route('import') }}",
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'BERHASIL!',
                            text: 'DATA BERHASIL DI IMPORT!',
                            showConfirmButton: false,
                            timer: 3000
                        }).then(function() {
                            $('#importModal').modal('hide');
                            var dataTable = $('#table-menu').DataTable();
                            dataTable.ajax.reload();
                            $('#file').val('');
                        });
                    },
                    error: function(xhr, status, error) {
                        handleAjaxError(xhr);
                    }
                });
            });
        });

        function formatRupiah(angka) {
            let rupiah = '';
            let angkarev = angka.toString().split('').reverse().join('');
            for (let i = 0; i < angkarev.length; i++)
                if (i % 3 == 0) rupiah += angkarev.substr(i, 3) + '.';
            return 'Rp ' + rupiah.split('', rupiah.length - 1).reverse().join('');
        }

        function destroy(id) {
            var id = id;
            var token = $("meta[name='csrf-token']").attr("content");

            Swal.fire({
                title: 'APAKAH KAMU YAKIN ?',
                text: "INGIN MENGHAPUS DATA INI!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'BATAL',
                confirmButtonText: 'YA, HAPUS!',
            }).then((result) => {
                if (result.isConfirmed) {
                    //ajax delete
                    jQuery.ajax({
                        url: `bahan_baku/${id}`,
                        data: {
                            "id": id,
                            "_token": token
                        },
                        type: 'DELETE',
                        success: function (response) {
                            if (response.status == "success") {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'BERHASIL!',
                                    text: 'DATA BERHASIL DIHAPUS!',
                                    showConfirmButton: false,
                                    timer: 3000
                                }).then(function () {
                                    var dataTable = $('#table-menu').DataTable();
                                    dataTable.ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'GAGAL!',
                                    text: 'DATA GAGAL DIHAPUS!',
                                    showConfirmButton: false,
                                    timer: 3000
                                }).then(function () {
                                    var dataTable = $('#table-menu').DataTable();
                                    dataTable.ajax.reload();
                                });
                            }
                        }
                    });
                }
            })
        }
    </script>
@endsection
