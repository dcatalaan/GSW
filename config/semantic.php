<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Semantic Search Configuration
    |--------------------------------------------------------------------------
    */
    'embedding_url' => env('EMBEDDING_URL', 'http://localhost:8000/embed'),
    'model' => env('EMBEDDING_MODEL', 'all-MiniLM-L6-v2'),
    'dimensions' => env('EMBEDDING_DIMENSIONS', 384),

    /*
    | --------------------------------------------------------------------------
    | Search weights for hybrid search
    | --------------------------------------------------------------------------
    | semantic_weight: peso de la búsqueda semántica (0.0 - 1.0)
    | text_weight: peso de la búsqueda textual (0.0 - 1.0)
    */
    'semantic_weight' => 0.7,
    'text_weight' => 0.3,

    /*
    | --------------------------------------------------------------------------
    | Minimum similarity score threshold
    | --------------------------------------------------------------------------
    */
    'min_similarity_score' => 0.3,

    /*
    | --------------------------------------------------------------------------
    | Maximum results per search
    | --------------------------------------------------------------------------
    */
    'max_results' => 20,
];
