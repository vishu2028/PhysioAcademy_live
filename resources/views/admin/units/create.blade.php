@extends('layouts.admin')


@section('content')
    <div class="container-fluid">
        <h4 class="mb-4">Add Unit</h4>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.units.store') }}" method="POST">
                    @csrf

                    @include('admin.units._form')

                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>

                    <a href="{{ route('admin.units.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>
@endsection
