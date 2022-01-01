<template>
    <div>
        <div class="flex-grow">
            <div class="max-w-sm md:max-w-none max-h-128 overflow-auto">
                <div class="border-b border-gray-200 shadow">
                    <table class="w-full">
                        <slot></slot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'Table',
    }
</script>
