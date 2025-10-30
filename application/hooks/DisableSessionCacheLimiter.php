<?php
function disable_session_cache_limiter() {
    if (function_exists('session_cache_limiter')) {
        // kosongkan supaya PHP TIDAK menyuntik no-store / pragma / expires default
        @session_cache_limiter('');
    }
}
