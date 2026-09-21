<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->text('embedding'); // JSON array de floats (SQLite)
            $table->string('model_name')->default('all-MiniLM-L6-v2');
            $table->timestamp('created_at')->nullable();

            $table->index('product_id');
        });

        // Crear índice HNSW solo en PostgreSQL (pgvector)
        if (config('database.default') === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
            DB::statement('
                CREATE INDEX product_embeddings_embedding_idx
                ON product_embeddings
                USING hnsw (embedding vector_cosine_ops)
                WITH (m = 16, ef_construction = 64)
            ');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_embeddings');
    }
};
