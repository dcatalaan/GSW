@extends('layouts.app')

@section('title', 'Categorías Admin — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header-row">
            <div>
                <h1 class="section-title">Categorías</h1>
                <p class="section-subtitle">{{ $categories->count() }} categorías</p>
            </div>
        </div>

        {{-- Formulario nueva categoría --}}
        <div class="admin-form-card">
            <h3>Nueva Categoría</h3>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="inline-form">
                @csrf
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <input type="text" name="name" class="form-input" placeholder="Nombre de la categoría" required>
                    </div>
                    <div class="form-group" style="flex: 3;">
                        <input type="text" name="description" class="form-input" placeholder="Descripción (opcional)">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Crear</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>{{ $category->description ?? '—' }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td>
                                <form action="{{ route('admin.categories.delete', $category->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs"
                                        onclick="return confirm('¿Eliminar esta categoría? Los productos asociados también se eliminarán.')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No hay categorías</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
