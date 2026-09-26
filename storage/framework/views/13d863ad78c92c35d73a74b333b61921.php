@script
<script>
    Livewire.on('passkeyPropertiesValidated', async function (eventData) {
        const passkeyOptions = eventData[0].passkeyOptions;

        const passkey = await startRegistration({ optionsJSON: passkeyOptions });

        @this.call('storePasskey', JSON.stringify(passkey));
    });
</script>
@endscript
<?php /**PATH C:\Users\Paul Pastor\DGConstruction\vendor\spatie\laravel-passkeys\resources\views\livewire\partials\createScript.blade.php ENDPATH**/ ?>