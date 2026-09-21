<?php

use Illuminate\Support\Facades\Schedule;

// Ejecutar la regeneración de embeddings diariamente a las 3 AM
// Schedule::command('embeddings:regenerate')->dailyAt('03:00');

// Limpiar caché de la aplicación semanalmente
// Schedule::command('cache:prune-stale-tags')->weekly();
