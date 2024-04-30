import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    darkMode : 'class',
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './resources/views/livewire/**/*.blade.php',

        './resources/views/components/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
