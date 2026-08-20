@extends('layouts.admin')

@section('title', 'Units')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Units</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <x-admin.data-table
        title="Units List"
        :headers="['#', 'Unit', 'Subject', 'Sort Order', 'Status', 'Actions']"
        :createRoute="route('admin.units.create')"
    >

        @foreach($units as $unit)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    <strong>{{ $unit->name }}</strong>

                    @if($unit->description)
                        <div class="text-muted small">
                            {{ Str::limit($unit->description, 80) }}
                        </div>
                    @endif
                </td>

                <td>
                    {{ $unit->subject?->name ?? '-' }}
                </td>

                <td>
                    {{ $unit->sort_order }}
                </td>

                <td>
                    <form action="{{ route('admin.units.toggle-status', $unit) }}"
                          method="POST">

                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="btn btn-sm {{ $unit->is_active ? 'btn-success' : 'btn-secondary' }}">

                            {{ $unit->is_active ? 'Active' : 'Inactive' }}

                        </button>

                    </form>
                </td>

                <td>

                    <a href="{{ route('admin.units.edit', $unit) }}"
                       class="btn btn-sm btn-warning">

                        Edit

                    </a>

                    <form action="{{ route('admin.units.destroy', $unit) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this unit?')">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="btn btn-sm btn-danger">

                            Delete

                        </button>

                    </form>

                </td>

            </tr>

        @endforeach

    </x-admin.data-table>

</div>

@endsection