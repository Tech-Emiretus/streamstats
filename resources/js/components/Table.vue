<template>
    <div class="container flex justify-center mx-auto">
        <div class="flex flex-grow">
            <div class="w-full max-h-128 overflow-auto">
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
