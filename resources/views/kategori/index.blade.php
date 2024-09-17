@extends('layouts.global')

@section('title')
    Kategori
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Halaman Kategori</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Tabel Kategori</h3>
                        <button type="button" class="btn btn-light" data-toggle="modal" data-target="#addModal">
                            Tambah Data
                        </button>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <table class="table table-bordered table-md" id="table-kategori">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kategori</th>
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
                        <label for="field_name">Nama Kategori</label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama">
                        @error('nama')
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
                        <label for="field_name">Nama Kategori</label>
                        <input type="text" class="form-control @error('nama_kategori') is-invalid @enderror" id="nama_kategorian" name="nama_kategori">
                        @error('nama_kategori')
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
                url: '{{ url("categories") }}/' + id + '/edit',
                type: 'GET',
                success: function (response) {

                    console.log(response);

                    if (response) {
                        // Isi formulir modal dengan data yang diterima dari server
                        $('#editForm').attr('data-id', response.id); // Simpan ID pengeluaran
                        $('#nama_kategorian').val(response.nama);
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
                nama: $('#nama_kategorian').val()
            };

            $(".form-control").removeClass('is-invalid');
            $(".invalid-feedback").remove();

            // Lakukan pembaruan melalui AJAX
            $.ajax({
                url: '{{ url("/categories") }}/' + kategoriId,
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
                            var dataTable = $('#table-kategori').DataTable();
                            dataTable.ajax.reload();
                        });
                        // Tutup modal setelah pembaruan berhasil


                        // Tampilkan pesan sukses atau lakukan tindakan lainnya
                        console.log('Pembaruan berhasil!');
                    } else {
                        console.error('Pembaruan gagal: ' + response.message || 'Terjadi kesalahan.');
                    }
                },
                error: function (xhr, status, error) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var idMappings = {
                            'no_urut': 'no_urutan',
                            'nama_kategori': 'nama_kategorian'
                            // Tambahkan kunci dan id lainnya jika diperlukan
                        };
                        // Display validation errors
                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                var errorMessage = errors[key][0];
                                var fieldId = idMappings[key] || key;
                                $('#' + fieldId).addClass('is-invalid');
                                $('#' + fieldId).after('<div class="invalid-feedback">' + errorMessage + '</div>');
                            }
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

            var table = $('#table-kategori').DataTable({
                // processing: true,
                serverSide: true,
                responsive: true,
                searching: false,
                paging: false,
                info: false,
                ordering: false,
                ajax: {
                    url: "{{ route('categories.index') }}",
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'nama', name: 'nama' },
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
                    url: '{{ route('categories.store') }}',
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
                            var dataTable = $('#table-kategori').DataTable();
                            dataTable.ajax.reload();
                            $('#no_urut').val('');
                            $('#nama_kategori').val('');
                        });

                    },
                    error: function(xhr, status, error) {
                        handleAjaxError(xhr);
                    }
                });
            });
        });



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
                        url: `categories/${id}`,
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
                                    var dataTable = $('#table-kategori').DataTable();
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
                                    var dataTable = $('#table-kategori').DataTable();
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
