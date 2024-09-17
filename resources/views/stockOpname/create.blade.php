@extends('layouts.global')

@section('title')
    Create Stock Opname
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Create Stock Opname</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Form Stock Opname</h3>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <form action="{{ route('stockOpname.store') }}" method="POST" id="opnameForm">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Bahan Baku</th>
                                                <th>Stok Fisik</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bahanBakus as $bahanBaku)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $bahanBaku->bahanBaku->bahan_baku }}</td>
                                                    <td>
                                                        <input type="number" name="opnames[{{ $bahanBaku->bahanBaku->id }}][stok_fisik]" class="form-control stok-fisik" min="0" required data-id="{{ $bahanBaku->bahanBaku->id }}">
                                                        <input type="hidden" name="opnames[{{ $bahanBaku->bahanBaku->id }}][bahan_baku_id]" value="{{ $bahanBaku->bahanBaku->id }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                {{ $bahanBakus->links('pagination::bootstrap-4') }} <!-- Pagination links -->
                                <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('opnameForm');
        const inputs = document.querySelectorAll('.stok-fisik');

        // Load values from localStorage
        inputs.forEach(input => {
            const id = input.getAttribute('data-id');
            const value = localStorage.getItem(`stok_fisik_${id}`);
            if (value) {
                input.value = value;
            }

            // Save value to localStorage on change
            input.addEventListener('input', () => {
                localStorage.setItem(`stok_fisik_${id}`, input.value);
            });
        });

        // Clear localStorage and submit form on submit
        form.addEventListener('submit', (event) => {
            // Prevent default form submission
            event.preventDefault();

            // Append hidden inputs for all localStorage items
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith('stok_fisik_')) {
                    const id = key.replace('stok_fisik_', '');
                    const value = localStorage.getItem(key);

                    // Create hidden input for each localStorage item
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `opnames[${id}][stok_fisik]`;
                    hiddenInput.value = value;
                    form.appendChild(hiddenInput);

                    // Create hidden input for bahan_baku_id
                    const hiddenBahanBakuId = document.createElement('input');
                    hiddenBahanBakuId.type = 'hidden';
                    hiddenBahanBakuId.name = `opnames[${id}][bahan_baku_id]`;
                    hiddenBahanBakuId.value = id;
                    form.appendChild(hiddenBahanBakuId);
                }
            });

            // Clear localStorage
            localStorage.clear();

            // Submit the form
            form.submit();
        });
    });
</script>
@endsection
@endsection
