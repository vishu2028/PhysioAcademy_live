@extends('layouts.admin')

@section('title', 'Material Categories')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Material Categories</h2>
        <p class="text-secondary mb-0">
            Categories are shared by Exam Aid materials and topic learning materials. Each category has its own public page.
        </p>
    </div>

    <x-admin.data-table
        title="Category List"
        :headers="['Name', 'Slug / Page', 'Exam Aid', 'Topics', 'Order', 'Status', 'Actions']"
        :createRoute="route('admin.exam-aid-categories.create')"
        createText="Add Category"
    >
        @foreach($categories as $category)
            <tr>
                <td class="ps-4 fw-bold">{{ $category->name }}</td>
                <td>
                    <a href="{{ $category->public_url }}" target="_blank" rel="noopener" class="small text-decoration-none">
                        /exam-aid/category/{{ $category->slug }}
                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </td>
                <td>{{ $category->materials_count }}</td>
                <td>{{ $category->learning_materials_count }}</td>
                <td>{{ $category->sort_order }}</td>
                <td>
                    @if($category->is_active)
                        <span class="badge bg-success-subtle text-success px-3">Active</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.exam-aid-categories.edit', $category) }}" class="btn btn-primary btn-sm rounded-3">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.exam-aid-categories.destroy', $category) }}" method="POST"
                              onsubmit="return confirm('Delete this category? Its materials will become uncategorized.')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
@endsection
