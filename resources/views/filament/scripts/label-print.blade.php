@if (! defined('LABEL_PRINT_SCRIPTS_LOADED'))
    @php(define('LABEL_PRINT_SCRIPTS_LOADED', true))
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
    <script>{!! file_get_contents(resource_path('js/direct-print.js')) !!}</script>
@endif
